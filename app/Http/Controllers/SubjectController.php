<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Level;
use App\Models\Subject;


class SubjectController extends Controller
{
    //ADMIN
    // 1) show subject names
    // 2) add subject in level (teacher + name )     DONE
    // 3) show teacher to add subject                DONE
    // 4) upload Curriculum
    // 5) show subject detail
    // 6) edit Curriculum

    // TEACHER
    // 1) show subject (with his)
    // 2) show subject details (all)
    // 3) add extention
    // 4) detete extention

    // STUDENT
    // 1) show subject
    // 2) show details
    // 3) request book

    // SUBADMIN
    // 1) show book requests


    // __________ Admin api ___________

    public function getTeachers() {

        $teachers = User::where('role', 'teacher')
                        ->join('teachers', 'users.id', '=', 'teachers.userID')
                        ->select(
                            'teachers.id as id', // Get ID from teachers table
                            'users.firstAndLastName'     // Get name from users table
                        )
                        ->get();

        return response()->json([
            'teachers' => $teachers
        ]);
    }


    public function addSubject(Request $request){

        $validated = $request->validate([
                'subjectName' => 'required|string',
                'teacherID' => 'required|exists:teachers,id',
                'levelName' => 'required|in:introductory,level1,level2,level3,level4,level5,level6',
                'courseID' => 'required|exists:courses,id',
        ]);

        $level = Level::where('courseID', $validated['courseID'])
                    ->where('levelName', $validated['levelName'])
                    ->first();

        if (!$level) {
            return response()->json([
                'message' => 'Level not found for this course'
            ], 404);
        }

         if (Subject::where('subjectName', $validated['subjectName'])
                ->where('teacherID', $validated['teacherID'])
                ->exists()) {
            return response()->json([
                'message' => 'Subject Already Created'
            ], 409);
        }

        $subject = Subject::create([
            'subjectName' => $validated['subjectName'],
            'levelID' => $level->id,
            'teacherID' => $validated['teacherID'],
        ]);

        return response()->json([
            'message' => 'Subject created successfully',
            'subject' => $subject
        ], 201);

    }


    public function getSubjects($courseID ,$levelName){
        $level = Level::where('courseID', $courseID)
                    ->where('levelName', $levelName)
                    ->first();

        $subjects = Subject::where('levelID', $level->id)->pluck('subjectName');

        return response()->json([
            'subjects' => $subjects
        ]);
    }



}
