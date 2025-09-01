<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Level;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Subject;

class SearchController extends Controller
{
    //

    public function searchInLevel($courseID, $levelName, $search) {
        try {
            $level = Level::where('courseID',$courseID)
                        ->where('levelName', $levelName)
                        ->first();

            if (!$level) {
                return response()->json([
                    'message' => 'Level not found.'
                ], 404);
            }

            $levelID = $level->id;

            $students = Student::whereHas('levels', function($q) use ($levelID) {
                    $q->where('levelID', $levelID);
                })
                ->when($search, function($query, $search) {
                    return $query->whereHas('user', function($q) use ($search) {
                        $q->where('firstAndLastName', 'LIKE', "%$search%");
                    });
                })
                ->with(['user:id,firstAndLastName'])
                ->get(['id', 'userID']);

            return response()->json([
                'search_term' => $search ?? 'all',
                'students' => $students->map(function($student) {
                    return [
                        'id' => $student->id,
                        'firstAndLastName' => $student->user->firstAndLastName
                    ];
                }),
                'count' => $students->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to search students',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function searchStudentTeacherInSystem($search){
        $students = Student::when($search, function($query, $search) {
                return $query->whereHas('user', function($q) use ($search) {
                    $q->where('firstAndLastName', 'LIKE', "%$search%");
                });
            })
            ->get(['id', 'userID']);

        $teachers = Teacher::when($search, function($query, $search) {
                return $query->whereHas('user', function($q) use ($search) {
                    $q->where('firstAndLastName', 'LIKE', "%$search%");
                });
            })
            ->get(['id', 'userID']);

        return response()->json([
            'search_term' => $search ?? 'all',
            'students' => $students->map(function($student) {
                return [
                    'id' => $student->id,
                    'firstAndLastName' => $student->user->firstAndLastName,
                    'fatherName' => $student->user->fatherName,
                    'role' => $student->user->role,
                    'phoneNumber' => $student->user->phoneNumber,
                    'email' => $student->user->email
                ];
            }),
            'teachers' => $teachers->map(function($teacher) {
                return [
                    'id' => $teacher->id,
                    'firstAndLastName' => $teacher->user->firstAndLastName,
                    'fatherName' => $teacher->user->fatherName,
                    'role' => $teacher->user->role,
                    'phoneNumber' => $teacher->user->phoneNumber,
                    'email' => $teacher->user->email
                ];
            }),
            'count' => $teachers->count()+$students->count()
        ]);
    }

    public function searchSubject($search) {
        $subjects = Subject::when($search, function($query, $search) {
                return
                    $query->where('subjectName', 'LIKE', "%$search%");

            })
            ->get();

        return response()->json([
            'subjects' => $subjects->map(function($subject) {
                return [
                    'id' => $subject->id,
                    'subjectName' => $subject->subjectName,
                    'curriculum' => $subject->curriculum
                ];
            }),
        ]);
    }

}
