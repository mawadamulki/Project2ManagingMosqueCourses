<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\User;
use App\Models\Level;
use App\Models\Subject;
use App\Models\Presence;
use App\Models\Course;
use App\Models\Teacher;


class GetFuncController extends Controller
{
    //

    public function getStudentInfoInLevel($studentID, $courseID, $levelName) {

        $student = Student::where('id', $studentID)
            ->select(
                'id as StudentID',
                'studyOrCareer',
                'magazeh',
                'PreviousCoursesInOtherPlace',
                'isPreviousStudent',
                'previousCourses',
                'userID'
            )
            ->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }


        $user = User::where('id', $student->userID)
            ->select(
                'email',
                'firstAndLastName',
                'fatherName',
                'phoneNumber',
                'birthDate',
                'address'
            )
            ->first();

        $level = Level::where('courseID',$courseID)
                ->where('levelName', $levelName)
                ->first();

        if (!$level) {
            return response()->json([
                'message' => 'Level not found.'
            ], 404);
        }

        $subjects = Subject::where('levelID', $level->id)
            ->select('id', 'subjectName')
            ->get();

        $subjectsWithPresence = $subjects->map(function ($subject) use ($studentID) {
            $totalClasses = Presence::where('subjectID', $subject->id)
                ->distinct('date')
                ->count('date');

            $attendedClasses = Presence::where([
                    'subjectID' => $subject->id,
                    'studentID' => $studentID
                ])
                ->count();

            return [
                'subject_id' => $subject->id,
                'subject_name' => $subject->subjectName,
                'presence_rate' => "$attendedClasses/$totalClasses"
            ];
        });

        $mergedData = array_merge(
            $student->toArray(),
            $user->toArray()
        );


        return response()->json([
            'data' => $mergedData,
            'presence' => $subjectsWithPresence
        ]);

    }


    public function getAllStudents(){
        $students = DB::table('students')
            ->join('users', 'students.userID', '=', 'users.id')
            ->select([
                'students.id as studentID',
                'users.firstAndLastName as firstAndLastName'
            ])
            ->get();

        return response()->json([
            'students' => $students
        ]);
    }

    public function deleteStudentAccount($studentID){

        $student = Student::findOrFail($studentID);
        $student->user()->delete();
        $student->delete();

        return response()->json([
            'message' => 'student deleted succesfully'
        ], 200);

    }

    public function getCoursesForStudent($studentID){
        $student = Student::where('id', $studentID)
            ->select(
                'id as StudentID',
                'studyOrCareer',
                'magazeh',
                'PreviousCoursesInOtherPlace',
                'isPreviousStudent',
                'previousCourses',
                'userID'
            )
            ->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }


        $user = User::where('id', $student->userID)
            ->select(
                'email',
                'firstAndLastName',
                'fatherName',
                'phoneNumber',
                'birthDate',
                'address'
            )
            ->first();

            $mergedData = array_merge(
            $student->toArray(),
            $user->toArray()
        );


        $courses = Course::select('courses.*','levels.levelName')
                    ->join('levels', 'levels.courseID', '=', 'courses.id')
                    ->join('level_student_pivot', 'level_student_pivot.levelID', '=', 'levels.id')
                    ->where('level_student_pivot.studentID', $studentID)
                    ->distinct()
                    ->get();


        return response()->json([
            'data' => $mergedData,
            'courses' => $courses
        ]);
    }

    public function getCourseDetailForStudent($studentID, $courseID, $levelName) {
        try {

            $level = Level::where('courseID',$courseID)
                ->where('levelName', $levelName)
                ->first();

            if (!$level) {
                return response()->json([
                    'message' => 'Level not found.'
                ], 404);
            }

            Student::findOrFail($studentID);

            $results = DB::table('subjects')
                ->leftJoin('results', function($join) use ($studentID) {
                    $join->on('subjects.id', '=', 'results.subjectID')
                        ->where('results.studentID', '=', $studentID);
                })
                ->where('subjects.levelID', $level->id)
                ->select([
                    'subjects.id as subject_id',
                    'subjects.subjectName',
                    'results.test',
                    'results.exam',
                    'results.presenceMark as presence',
                    'results.total',
                    'results.status'
                ])
                ->get()
                ->map(function ($item) {
                    return [
                        'subject_id' => $item->subject_id,
                        'subject_name' => $item->subjectName,
                        'marks' => $item->test !== null ? [
                            'test' => $item->test,
                            'exam' => $item->exam,
                            'presence' => $item->presence,
                            'total' => $item->total,
                            'status' => $item->status
                        ] : null
                    ];
                });

            return response()->json([
                'data' => $results
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve marks',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }



    // __________________________________________

    public function getAllTeachers(){
        $teacher = DB::table('teachers')
            ->join('users', 'teachers.userID', '=', 'users.id')
            ->select([
                'teachers.id as teacherID',
                'users.firstAndLastName as firstAndLastName'
            ])
            ->get();

        return response()->json([
            'teachers' => $teacher
        ]);
    }


    public function deleteTeacherAccount($teacherID){

        $teacher = Teacher::findOrFail($teacherID);
        $teacher->user()->delete();
        $teacher->delete();

        return response()->json([
            'message' => 'teacher deleted succesfully'
        ], 200);

    }


    public function getCoursesForTeacher($teacherID){
        $teacher = Teacher::where('id', $teacherID)
            ->select(
                'id as teacherID',
                'studyOrCareer',
                'magazeh',
                'PreviousExperience',
                'userID'
            )
            ->first();

        if (!$teacher) {
            return response()->json(['error' => 'teacher not found'], 404);
        }


        $user = User::where('id', $teacher->userID)
            ->select(
                'email',
                'firstAndLastName',
                'fatherName',
                'phoneNumber',
                'birthDate',
                'address'
            )
            ->first();

            $mergedData = array_merge(
            $teacher->toArray(),
            $user->toArray()
        );


        $courses = DB::table('courses')
                    ->join('levels', 'courses.id', '=', 'levels.CourseID')
                    ->join('subjects', 'subjects.levelID', '=', 'levels.id')
                    ->join('teachers', 'teachers.id', '=', 'subjects.teacherID')
                    ->select([
                        'courses.id',
                        'courses.courseName',
                        'courses.status',
                        'courses.courseImage',
                        'courses.created_at',
                        'courses.updated_at'
                    ])
                    ->where('subjects.TeacherID', $teacherID)
                    ->distinct()
                    ->get();



        return response()->json([
            'data' => $mergedData,
            'courses' => $courses
        ]);
    }






}
