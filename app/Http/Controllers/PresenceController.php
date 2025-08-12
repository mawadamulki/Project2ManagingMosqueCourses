<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PresenceController extends Controller
{
    // Subadmin
    // 1) add presence
    // 2) get student in level
    // 3) get presence



    public function getStudentInLevel($subjectID) {

        $subject = Subject::find($subjectID);
        if (!$subject) {
            return response()->json(['error' => 'Subject not found'], 404);
        }


        $students = DB::table('level_student_pivot')
            ->join('students', 'level_student_pivot.studentID', '=', 'students.id')
            ->join('users', 'students.userID', '=', 'users.id')
            ->where('level_student_pivot.levelID', $subject->levelID)
            ->select([
                'students.id as studentID',
                'users.firstAndLastName as firstAndLastName'
            ])
            ->get();

        return response()->json([
            'students' => $students
        ]);
    }


    public function addPresence(Request $request) {
        $validator = Validator::make($request->all(), [
            'subjectID' => 'required|exists:subjects,id',
            'date' => 'required|string',
            'studentIDs' => 'required|array',
            'studentIDs.*' => 'exists:students,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $existingCount = Presence::where('subjectID', $request->subjectID)
            ->where('date', $request->date)
            ->whereIn('studentID', $request->studentIDs)
            ->count();

        if ($existingCount > 0) {
            return response()->json([
                'message' => 'Some students already have presence recorded for this subject and date',
                'conflict_count' => $existingCount
            ], 409);
        }

        $presences = [];
        $now = now();

        foreach ($request->studentIDs as $studentID) {
            $presences[] = [
                'subjectID' => $request->subjectID,
                'date' => $request->date,
                'studentID' => $studentID,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        DB::transaction(function () use ($presences) {
            Presence::insert($presences);
        });

        return response()->json([
            'message' => 'Presences recorded successfully',
            'count' => count($presences)
        ], 201);

    }


    public function getPresence($subjectID) {

        $presencesByDate = Presence::where('subjectID', $subjectID)
            ->with(['student.user:id,firstAndLastName'])
            ->get()
            ->groupBy('date');


        $result = $presencesByDate->map(function ($presences, $date) {
            return [
                'date' => $date,
                'students' => $presences->map(function ($presence) {
                    return [
                        'student_id' => $presence->studentID,
                        'student_name' => $presence->student->user->firstAndLastName
                    ];
                })
            ];
        })->values();

        return response()->json([
            'subjectID' => $subjectID,
            'presence' => $result
        ]);
    }


    public function getSubjects($courseID, $levelName){
        $level = Level::where('courseID',$courseID)
                    ->where('levelName', $levelName)
                    ->first();

        if (!$level) {
            return response()->json([
                'message' => 'Level not found.'
            ], 404);
        }

        $subjects = Subject::where('levelID', $level->id)
        ->select('id','subjectName','levelID','teacherID')->get();

        return response()->json([
            'subjects' => $subjects
        ]);

    }



}
