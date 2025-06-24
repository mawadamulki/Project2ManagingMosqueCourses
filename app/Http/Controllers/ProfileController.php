<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProfileController extends Controller
{
    public function showDetailesForStudent()
    {
        $user = Auth::user();

        $user->load(['userProfile', 'student']);
        if ($user->role === 'student') {

            $data = [
                'id' => $user->id,
                'name' => $user->firstAndLastName,
                'fatherName' => $user->fatherName,
                'email' => $user->email,
                // 'role' => $user->role,
                'profile_image' => $user->userProfile?->profile_image,
                'phoneNumber' => $user->phoneNumber,
                'address' => $user->address,
                'birthDate' => $user->birthDate
            ];

            $user->load([
                'courses.levels.subjects',
                'marks'
            ]);

            $studentInfo = $user->student;

            $data['student_info'] = [

                'studyOrCareer' => $studentInfo->studyOrCareer,
                'magazeh' => $studentInfo->magazeh,
                'PreviousCoursesInOtherPlace' => $studentInfo->PreviousCoursesInOtherPlace,
                'isPreviousStudent' => $studentInfo->isPreviousStudent,
                'previousCourses' => $studentInfo->previousCourses,
            ];

            $data['course'] = $user->courses->map(function ($course) use ($user) {
                return [
                    'id' => $course->id,
                    'courseName' => $course->title ?? $course->courseName,
                    'levels' => $course->levels->map(function ($level) use ($user) {
                        return [
                            'id' => $level->id,
                            'levelName' => $level->name,
                            'subjects' => $level->subjects->map(function ($subject) use ($user) {
                                $mark = $user->marks->where('subject_id', $subject->id)->first();
                                return [
                                    'id' => $subject->id,
                                    'subjectName' => $subject->subjectName,
                                    'mark' => $mark?->mark,
                                ];
                            }),
                        ];
                    }),
                ];
            });
            return response()->json([
                // 'status' => true,
                'data' => $data
            ], 200);
        } else {
            return response()->json(['message' => 'User not found'],  404);
        }
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
                                return $subject->teacher && $subject->teacher->user_id === $user->id;
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


    public function showDetailesForSupervisor()
    {

        $user = Auth::user();
        $user->load(['userProfile', 'supervisor']);
        if ($user->role === 'supervisor') {

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

            $supervisorInfo = $user->supervisor;

            $data['supervisor_info'] = [
                'studyOrCareer' => $supervisorInfo->studyOrCareer,
                'magazeh' => $supervisorInfo->magazeh,
                'PreviousExperience' => $supervisorInfo->PreviousExperience,
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
    public function showDetailesForAdmin()
    {

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
    public function showUserProfileByAdmin($id)
    {
        try {
            $authUser = Auth::user();

            if (!$authUser || $authUser->role !== 'admin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $user = User::with(['student', 'teacher', 'supervisor', 'userProfile'])->findOrFail($id);

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

                case 'supervisor':
                    if ($user->supervisor) {
                        $response['supervisorProfile'] = $user->supervisor;
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
}
