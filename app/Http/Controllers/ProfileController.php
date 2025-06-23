<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{

    public function show()
    {
        $user = Auth::user();

        $user->load(['userProfile']);

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'profile_image' => $user->userProfile?->profile_image,
        ];

        switch ($user->role) {
            case 'student':
                $user->load(['courses.subjects', 'marks']);
                $data['courses'] = $user->courses->map(function ($course) use ($user) {
                    return [
                        'id' => $course->id,
                        'courseName' => $course->title,
                        'subjects' => $course->subjects->map(function ($subject) use ($user) {
                            $mark = $user->marks->where('subject_id', $subject->id)->first();
                            return [
                                'id' => $subject->id,
                                'subjectName' => $subject->subjectName,
                                'mark' => $mark?->mark,
                            ];
                        }),
                    ];
                });
                break;

            case 'teacher':
                $user->load(['courses.subjects']);
                $data['courses'] = $user->courses->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'courseName' => $course->courseName,
                        'subjects_count' => $course->subjects->count(),
                    ];
                });
                break;

            case 'supervisor':
                $user->load('supervisedCourses.subjects');
                $data['supervised_courses'] = $user->supervisedCourses->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'courseName' => $course->courseName,
                        'subjects_count' => $course->subjects->count(),
                    ];
                });
                break;

            case 'admin':
                $data['note'] = 'Admin profile - system access';
                break;
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ], 200);
    }


public function showUserProfileForAdmin($id)
{
    try {
        // السماح فقط للمدير
        // if (auth()->user()->role!=='admin') {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        // جلب المستخدم والعلاقة حسب الدور
        $user = User::with(['student', 'teacher', 'supervisor'])->findOrFail($id);

        // دمج البيانات في استجابة واحدة
        $response = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];

        // دمج بيانات الملف الشخصي حسب الدور
        if ($user->role === 'student' && $user->student) {
            $response = array_merge($response, $user->student->toArray());
        } elseif ($user->role === 'supervisor' && $user->supervisor) {
            $response = array_merge($response, $user->supervisor->toArray());
        } elseif ($user->role === 'teacher' && $user->teacher) {
            $response = array_merge($response, $user->teacher->toArray());
        }

        return response()->json($response);
    } catch (Exception $e) {
        return response()->json(['message' => 'User not found'], 404);
    } catch (\Throwable $e) {
        return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
    }
}



}
