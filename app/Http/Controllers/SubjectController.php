<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Level;
use App\Models\Subject;
use App\Models\Curriculum;

class SubjectController extends Controller
{
    //  ________ADMIN________
    // 1) show subject names                         DONE
    // 2) add subject in level (teacher + name )     DONE
    // 3) show teacher to add subject                DONE
    // 4) upload Curriculum                          DONE
    // 5) show subject detail                        DONE
    // 6) edit Curriculum                            DONE

    //  _______TEACHER________
    // 1) show subject (with his)
    // 2) show subject details (all)
    // 3) add extention
    // 4) detete extention

    //  _______STUDENT________
    // 1) show subject
    // 2) show details
    // 3) request book

    //  _______SUBADMIN________
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

    public function addCurriculum(Request $request){

        $validated = $request->validate([
            'subjectID' => 'required|exists:subjects,id',
            'curriculumFile' => 'required|file|mimes:pdf,doc,docx|max:51200' // 50MB max
        ]);

        if(Curriculum::where('subjectID',$validated['subjectID'])->first())
            return response()->json([
                    'message' => 'the subject already have curriculum , please update it'
            ]);

        $path = $request->file('curriculumFile')->store('curricula', 'public');

        $fullPath = asset('storage/' . $path);

        // Create curriculum record
        $curriculum = Curriculum::create([
            'subjectID' => $validated['subjectID'],
            'curriculumFile' => $fullPath
        ]);

        return response()->json([
            'message' => 'Curriculum uploaded successfully',
            'curriculum' => $curriculum
        ], 201);

    }


    public function updateCurriculum(Request $request){
        $validated = $request->validate([
            'subjectID' => 'required|exists:subjects,id',
            'curriculumFile' => 'required|file|mimes:pdf,doc,docx|max:51200'
        ]);

        $subject = Subject::findOrFail($validated['subjectID']);

        $curriculum = $subject->curriculum;
        if (!$subject->curriculum) {
            return response()->json([
                'message' => 'No curriculum found for this subject'
            ], 404);
        }

        if ($subject->curriculum->curriculumFile) {
            $oldPath = str_replace(asset('storage/'), '', $subject->curriculum->curriculumFile);
            Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('curriculumFile')->store('curricula', 'public');

        $fullPath = asset('storage/' . $path);

        $curriculum->update([
            'curriculumFile' => $fullPath
        ]);

        return response()->json([
            'message' => 'Curriculum updated successfully',
            'curriculum' => $curriculum,
            'file_size' => Storage::disk('public')->size($path)
        ]);

    }

    public function getSubjectDetails($courseID, $levelName){
        $subjects = DB::table('subjects')
            ->join('levels', 'subjects.levelID', '=', 'levels.id')
            ->join('teachers', 'subjects.teacherID', '=', 'teachers.id')
            ->join('users', 'teachers.userID', '=', 'users.id')
            ->leftJoin('curricula', 'subjects.id', '=', 'curricula.subjectID')
            ->select([
                'subjects.id',
                'subjects.subjectName',
                'teachers.id as teacher_id',
                'users.firstAndLastName as teacher_name',
                'curricula.id as curriculum_id',
                'curricula.curriculumFile'
            ])
            ->where('levels.courseID', $courseID)
            ->where('levels.levelName', $levelName)
            ->get();


        return response()->json([
            'subjects' => $subjects
        ]);
    }




    // __________ Teacher api _____________

    public function getSubject(){

    }





}
