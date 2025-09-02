<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Student;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\Subadmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProfileController extends Controller
{
    public function showDetailesForStudent() {
        $user = Auth::user();
        $studentInfo = $user->student;

        $data = [
            'id' => $user->id,
            'name' => $user->firstAndLastName,
            'fatherName' => $user->fatherName,
            'email' => $user->email,
            'profileImage' => $user->profileImage,
            'phoneNumber' => $user->phoneNumber,
            'address' => $user->address,
            'birthDate' => $user->birthDate,
            'studyOrCareer' => $studentInfo->studyOrCareer,
            'magazeh' => $studentInfo->magazeh,
            'PreviousCoursesInOtherPlace' => $studentInfo->PreviousCoursesInOtherPlace,
            'isPreviousStudent' => $studentInfo->isPreviousStudent,
            'previousCourses' => $studentInfo->previousCourses,
        ];

        return response()->json([
            'data' => $data
        ], 200);

    }


    public function showDetailesForTeacher()
    {
        $user = Auth::user();
        $user->load(['userProfile', 'teacher', 'courses.levels.subjects.teacher']);
        if ($user->role === 'teacher') {

            $data = [
                'id' => $user->id,
                'name' => $user->firstAndLastName,
                'fatherName' => $user->fatherName,
                'email' => $user->email,
                'profile_image' => $user->userProfile?->profile_image,
                'phoneNumber' => $user->phoneNumber,
                'address' => $user->address,
                'birthDate' => $user->birthDate
            ];

            $teacherInfo = $user->teacher;

            $data['teacher_info'] = [
                'studyOrCareer' => $teacherInfo->studyOrCareer,
                'magazeh' => $teacherInfo->magazeh,
                'PreviousExperience' => $teacherInfo->PreviousExperience,
            ];

            $data['courses'] = $user->courses->map(function ($course) use ($user) {
                return [
                    'id' => $course->id,
                    'courseName' => $course->title ?? $course->courseName,
                    'levels' => $course->levels->map(function ($level) use ($user) {
                        return [
                            'id' => $level->id,
                            'levelName' => $level->name,
                            'subjects' => $level->subjects->filter(function ($subject) use ($user) {
                                return $subject->teacher && $subject->teacher->userID === $user->id;
                            })->map(function ($subject) {
                                return [
                                    'id' => $subject->id,
                                    'subjectName' => $subject->subjectName,
                                    'teacherName' => $subject->teacher->user->firstAndLastName ?? null,
                                    'levelName' => $subject->level->name ?? null,
                                ];
                            }),
                        ];
                    }),
                ];
            });
            return response()->json($data, 200);
        } else {
            return response()->json(['message' => 'User not found'],  404);
        }
    }


    public function showDetailesForSubadmin()
    {

        $user = Auth::user();
        $user->load(['userProfile', 'subadmin']);
        if ($user->role === 'subadmin') {

            $data = [
                'id' => $user->id,
                'name' => $user->firstAndLastName,
                'fatherName' => $user->fatherName,
                'email' => $user->email,
                'profile_image' => $user->userProfile?->profile_image,
                'phoneNumber' => $user->phoneNumber,
                'address' => $user->address,
                'birthDate' => $user->birthDate
            ];

            $subadminInfo = $user->subadmin;

            $data['subadmin_info'] = [
                'studyOrCareer' => $subadminInfo->studyOrCareer,
                'magazeh' => $subadminInfo->magazeh,
                'PreviousExperience' => $subadminInfo->PreviousExperience,
            ];

            // $data['courses'] = $user->courses->map(function ($course) use ($user) {
            //     return [
            //         'id' => $course->id,
            //         'courseName' => $course->title ?? $course->courseName,
            //     ];
            // });
            return response()->json($data, 200);
        } else {
            return response()->json(['message' => 'User not found'],  404);
        }
    }
    public function showDetailesForAdmin() {

        $user = Auth::user();
        $user->load(['userProfile', 'admin']);
        if ($user->role === 'admin') {

            $data = [
                'id' => $user->id,
                'name' => $user->firstAndLastName,
                'fatherName' => $user->fatherName,
                'email' => $user->email,
                'profile_image' => $user->userProfile?->profile_image,
                'phoneNumber' => $user->phoneNumber,
                'address' => $user->address,
                'birthDate' => $user->birthDate
            ];

            return response()->json($data, 200);
        } else {
            return response()->json(['message' => 'User not found'],  404);
        }
    }

    public function showUserProfileByAdmin($id)
    {
        try {
            $authUser = Auth::user();

            if (!$authUser || $authUser->role !== 'admin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $user = User::with(['student', 'teacher', 'subadmin', 'userProfile'])->findOrFail($id);

            $response = [
                'id' => $user->id,
                'name' => $user->firstAndLastName,
                'fatherName' => $user->fatherName,
                'email' => $user->email,
                'phoneNumber' => $user->phoneNumber,
                'address' => $user->address,
                'birthDate' => $user->birthDate,
                'role' => $user->role,
                'profile_image' => $user->profile ? $user->profile->profile_image : null,
            ];

            switch ($user->role) {
                case 'student':
                    if ($user->student) {
                        $response['studentProfile'] = $user->student;
                    }
                    break;

                case 'teacher':
                    if ($user->teacher) {
                        $response['teacherProfile'] = $user->teacher;
                    }
                    break;

                case 'subadmin':
                    if ($user->subadmin) {
                        $response['subadminProfile'] = $user->subadmin;
                    }
                    break;
            }

            return response()->json($response);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found'], 404);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Unexpected error', 'error' => $e->getMessage()], 500);
        }
    }

    public function studentProfile(){
        $user = Auth::user();
        $studentID = Student::where('userID', $user->id)->get()->first();

        $student = Student::where('id', $studentID->id)
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

        $user = User::where('id', $student->userID)
            ->select(
                'email',
                'firstAndLastName',
                'fatherName',
                'phoneNumber',
                'birthDate',
                'address',
                'profileImage'
            )
            ->first();

            $mergedData = array_merge(
            $student->toArray(),
            $user->toArray()
        );

        $courses = Course::select('courses.*','levels.levelName')
                    ->join('levels', 'levels.courseID', '=', 'courses.id')
                    ->join('level_student_pivot', 'level_student_pivot.levelID', '=', 'levels.id')
                    ->where('level_student_pivot.studentID', $studentID->id)
                    ->distinct()
                    ->get();

        return response()->json([
            'data' => $mergedData,
            'courses' => $courses
        ]);
    }

    public function teacherProfile(){
        $user = Auth::user();
        $teacherID = Teacher::where('userID', $user->id)->get()->first();

        $teacher = Teacher::where('id', $teacherID->id)
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
                'address',
                'profileImage'
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
                        'levels.levelName'
                    ])
                    ->where('subjects.TeacherID', $teacherID->id)
                    ->distinct()
                    ->get();

        return response()->json([
            'data' => $mergedData,
            'courses' => $courses
        ]);
    }

    public function subadminProfile(){
        $user = Auth::user();
        $subadminID = Subadmin::where('userID', $user->id)->get()->first();

        $subadmin = Subadmin::where('id', $subadminID->id)
            ->select(
                'id as subadminID',
                'studyOrCareer',
                'magazeh',
                'PreviousExperience',
                'userID'
            )
            ->first();

        if (!$subadmin) {
            return response()->json(['error' => 'subadmin not found'], 404);
        }

        $user = User::where('id', $subadmin->userID)
            ->select(
                'email',
                'firstAndLastName',
                'fatherName',
                'phoneNumber',
                'birthDate',
                'address',
                'profileImage'
            )
            ->first();

            $mergedData = array_merge(
            $subadmin->toArray(),
            $user->toArray()
        );

        return response()->json([
            'data' => $mergedData
        ]);
    }

}
