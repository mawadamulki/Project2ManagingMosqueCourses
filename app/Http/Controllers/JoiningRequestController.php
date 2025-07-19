<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
    //تابع يضيف طالب للفل معين من الدورة    DONE


    //Joining Request to Course From Student to Admin
    public function createJoiningRequest($courseID){
        $user = Auth::user();

        $existingRequest = JoiningRequest::where('studentID', $user->student->id)
                                        ->where('courseID', $courseID)
                                        ->first();
        if ($existingRequest) {
            return response()->json([
                'message' => 'You have already sent a joining request for this course.'
            ], 409); // 409 Conflict
        }

        $courseNotExit = Course::where('id', $courseID)->first();
        if(!$courseNotExit){
            return response()->json([
                'message' => 'You Are Joining To Course Not Exiting.'
            ], 404); // 404 Not Found
        }

        $courseIsPrevious = Course::findOrFail($courseID);
        if($courseIsPrevious->status === 'previous'){
            return response()->json([
                'message' => 'Cannot join a course that has already ended.'
            ], 403); // 403 Forbidden
        }

        try {
        JoiningRequest::create([
            'studentID' => $user->student->id,
            'courseID' => $courseID
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



    public function getJoiningRequests($courseID){

        $joiningRequests = JoiningRequest::where('courseID', $courseID)
            ->with(['student.user:id,firstAndLastName'])
            ->get([
                'id',
                'studentID',
                'courseID',
                'status'
            ]);

        // Transform the data to include student name
        $formattedRequests = $joiningRequests->map(function ($request) {
            return [
                'id' => $request->id,
                'studentID' => $request->studentID,
                'courseID' => $request->courseID,
                'status' => $request->status,
                'student_name' => $request->student->user->firstAndLastName ?? 'Unknown'
            ];
        });

        return response()->json([
            'data' => $formattedRequests,
            'message' => 'Joining requests retrieved successfully.'
        ]);
    }

    public function getStudentInfo($studentID){
        $student = Student::where('id',$studentID)->select(
            'id',
            'studyOrCareer',
            'magazeh',
            'PreviousCoursesInOtherPlace',
            'isPreviousStudent',
            'previousCourses'
        )->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $userID = Student::where('id',$studentID)->select('userID')->first();
        $user = User::where('id',$userID->userID)->select(
            'email',
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


    public function enrollStudentToLevel($studentID, $courseID, $levelName){

        // Manually validate the parameters
        $validator = Validator::make([
            'studentID' => $studentID,
            'courseID' => $courseID,
            'levelName' => $levelName
        ], [
            'studentID' => 'required|exists:students,id',
            'courseID' => 'required|exists:courses,id',
            'levelName' => 'required|in:introductory,level1,level2,level3,level4,level5,level6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $student = Student::findOrFail($studentID);
            $course = Course::findOrFail($courseID);

            $requestedLevel = Level::firstOrCreate([
                'courseID' => $course->id,
                'levelName' => $levelName
            ]);

            // Check existing enrollment
            $currentEnrollment = DB::table('level_student_pivot')
                ->join('levels', 'level_student_pivot.levelID', '=', 'levels.id')
                ->where('level_student_pivot.studentID', $student->id)
                ->where('levels.courseID', $course->id)
                ->first(['levels.id as levelID', 'levels.levelName']);

            if ($currentEnrollment) {
                return response()->json([
                    'message' => 'Student is already enrolled in a level of this course',
                    'current_level' => [
                        'levelID' => $currentEnrollment->levelID,
                        'levelName' => $currentEnrollment->levelName
                    ],
                    'requested_level' => [
                        'levelID' => $requestedLevel->id,
                        'levelName' => $requestedLevel->levelName
                    ]
                ], 409);
            }

            // Enroll student
            DB::table('level_student_pivot')->insert([
                'studentID' => $student->id,
                'levelID' => $requestedLevel->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'message' => 'Student successfully enrolled in level',
                'data' => [
                    'studentID' => $student->id,
                    'courseID' => $course->id,
                    'levelID' => $requestedLevel->id,
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
