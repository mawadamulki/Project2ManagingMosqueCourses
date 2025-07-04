<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\JoiningRequest;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use App\Models\Level;

class JoiningRequestController extends Controller
{

    //تابع يعمل طلب اضافة من طالب                DONE
    //ادمن ينعرضلو طلبات الانضمام بس اسماء الطلاب   DONE
    //تابع يرجع معلومات الطالب مشان يحدد مستوى الطالب للادمن  DONE
    //تابع يضيف طالب للفل معين من الدورة


    //Joining Request to Course From Student to Admin
    public function createJoiningRequest($course_id){
        $user = Auth::user();

        $existingRequest = JoiningRequest::where('student_id', $user->student->id)
                                        ->where('course_id', $course_id)
                                        ->first();
        if ($existingRequest) {
            return response()->json([
                'message' => 'You have already sent a joining request for this course.'
            ], 409); // 409 Conflict
        }

        $courseNotExit = Course::where('id', $course_id)->first();
        if(!$courseNotExit){
            return response()->json([
                'message' => 'You Are Joining To Course Not Exiting.'
            ], 404); // 404 Not Found
        }

        $courseIsPrevious = Course::findOrFail($course_id);
        if($courseIsPrevious->status === 'previous'){
            return response()->json([
                'message' => 'Cannot join a course that has already ended.'
            ], 403); // 403 Forbidden
        }

        try {
        JoiningRequest::create([
            'student_id' => $user->student->id,
            'course_id' => $course_id
        ]);

            return response()->json([
                'message' => 'Joining Request Created Successfully.'
            ], 201); // 201 Created

        } catch (\Exception $e) {
            return response()->json([
                'error message' => 'Failed to send request: ' . $e->getMessage()
            ], 500); // 500 Internal Server Error
        }

    }



    public function getJoiningRequests($course_id){

        $joiningRequests = JoiningRequest::where('course_id', $course_id)
            ->with(['student.user:id,firstAndLastName'])
            ->get([
                'id',
                'student_id',
                'course_id',
                'status'
            ]);

        // Transform the data to include student name
        $formattedRequests = $joiningRequests->map(function ($request) {
            return [
                'id' => $request->id,
                'student_id' => $request->student_id,
                'course_id' => $request->course_id,
                'status' => $request->status,
                'student_name' => $request->student->user->firstAndLastName ?? 'Unknown'
            ];
        });

        return response()->json([
            'data' => $formattedRequests,
            'message' => 'Joining requests retrieved successfully.'
        ]);
    }

    public function getStudentInfo($student_id){
        $student = Student::where('id',$student_id)->select(
            'studyOrCareer',
            'magazeh',
            'PreviousCoursesInOtherPlace',
            'isPreviousStudent',
            'previousCourses'
        )->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $user_id = Student::where('id',$student_id)->select('user_id')->first();
        $user = User::where('id',$user_id->user_id)->select(
            'firstAndLastName',
            'fatherName',
            'phoneNumber',
            'birthDate',
            'address'
        )->first();


        $mergedData = array_merge(
            $student->toArray(),
            $user->toArray()
        );


        return response()->json([
            'data' => $mergedData,
        ]);

    }


    public function enrollStudentToLevel(Request $request){
        // Validate the request data
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'levelName' => 'required|in:introductory,level1,level2,level3,level4,level5,level6'
        ]);

        try {
            $student = Student::findOrFail($validated['student_id']);

            $course = Course::findOrFail($validated['course_id']);

            $requestedLevel = Level::firstOrCreate([
                'course_id' => $course->id,
                'levelName' => $validated['levelName']
            ]);

            // Check if student is already in any level of this course
            $currentEnrollment = DB::table('level_student_pivot')
                ->join('levels', 'level_student_pivot.level_id', '=', 'levels.id')
                ->where('level_student_pivot.student_id', $student->id)
                ->where('levels.course_id', $course->id)
                ->first(['levels.id as level_id', 'levels.levelName']);
            if ($currentEnrollment) {
                return response()->json([
                    'message' => 'Student is already enrolled in a level of this course',
                    'current_level' => [
                        'level_id' => $currentEnrollment->level_id,
                        'levelName' => $currentEnrollment->levelName
                    ],
                    'requested_level' => [
                        'level_id' => $requestedLevel->id,
                        'levelName' => $requestedLevel->levelName
                    ]
                ], 409);
            }

            // If not enrolled, add them to the requested level
            DB::table('level_student_pivot')->insert([
                'student_id' => $student->id,
                'level_id' => $requestedLevel->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'message' => 'Student successfully enrolled in level',
                'data' => [
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'level_id' => $requestedLevel->id,
                    'levelName' => $requestedLevel->levelName
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process enrollment',
                'error' => $e->getMessage()
            ], 500);
        }

    }


}
