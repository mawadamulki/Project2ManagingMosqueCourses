<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\User;
use App\Http\Models\Admin;
use App\Http\Models\Student;
use App\Http\Models\Teacher;
use Illuminate\Http\Request;
use App\Http\Middleware\role;
use App\Http\Models\Supervisor;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        // $data = $request->validate([

        //     'email' => 'required|email|unique:users',
        //     'password' => 'required|confirmed|min:8',
        //     'role' => 'required|in:admin,teacher,student,adminster',
        //     'firstAndLastName' => 'required|string',
        //     'fatherName' => 'required|string',

        //     // Teacher-specific validation
        //     'birthDate' => 'required_if:role,teacher',
        //     'address' => 'required_if:role,teacher',
        //     'study' => 'required_if:role,teacher',
        //     'career' => 'required_if:role,teacher',
        //     'magazeh' => 'required_if:role,teacher',
        //     'PreviousExperience' => 'required_if:role,teacher',

        //     // Student-specific validation
        //     'birthDate' => 'required_if:role,student',
        //     'address' => 'required_if:role,student',
        //     'study' => 'required_if:role,student',
        //     'career' => 'required_if:role,student',
        //     'magazeh' => 'required_if:role,student',
        //     'PreviousCoursesInOtherPlace' => 'required_if:role,student',
        //     'isPreviousStudent' => 'required_if:role,student',
        //     'previousCourses' => 'required_if:role,student',

        //     // Supervisor-specific validation
        //     'birthDate' => 'required_if:role,supervisor',
        //     'address' => 'required_if:role,supervisor',
        //     'study' => 'required_if:role,supervisor',
        //     'career' => 'required_if:role,supervisor',
        //     'magazeh' => 'required_if:role,supervisor',
        //     'PreviousExperience' => 'required_if:role,supervisor',

        // ]);

        // // Create base user
        // $user = User::create([
        //     'firstAndLastName' => $data['firstAndLastName'],
        //     'fatherName' => $data['fatherName'],
        //     'email' => $data['email'],
        //     'password' => Hash::make($data['password']),
        //     'role' => $data['role'],
        // ]);

        // // Create role-specific record
        // switch ($data['role']) {
        //     case 'teacher':
        //         $user->teacher()->create([
        //             'birthDate' => $data['birthDate'],
        //             'address' => $data['address'],
        //             'study' => $data['study'],
        //             'career' => $data['career'],
        //             'magazeh' => $data['magazeh'],
        //             'PreviousExperience' => $data['PreviousExperience']
        //         ]);
        //         break;

        //     case 'student':
        //         $user->student()->create([
        //             'birthDate' => $data['birthDate'],
        //             'address' => $data['address'],
        //             'study' => $data['study'],
        //             'career' => $data['career'],
        //             'magazeh' => $data['magazeh'],
        //             'PreviousCoursesInOtherPlace' => $data['PreviousCoursesInOtherPlace'],
        //             'isPreviousStudent' => $data['isPreviousStudent'],
        //             'previousCourses' => $data['previousCourses']
        //         ]);
        //         break;

        //     case 'supervisor':
        //         $user->teacher()->create([
        //             'birthDate' => $data['birthDate'],
        //             'address' => $data['address'],
        //             'study' => $data['study'],
        //             'career' => $data['career'],
        //             'magazeh' => $data['magazeh'],
        //             'PreviousExperience' => $data['PreviousExperience']
        //         ]);
        //         break;

        //     case 'admin':
        //     // Admin might not need extra fields, but you can add if needed
        //     $user->admin()->create();
        //     break;
        // }

        // $token = $user->createToken('auth_token')->plainTextToken;

        // return response()->json([
        //     'message' => 'Registration successful',
        //     'access_token' => $token
        // ], 201);


        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|regex:/[a-z]/|regex:/[0-9]/',
                'role' => ['required', Rule::in(['admin', 'teacher', 'student', 'supervisor'])],
                // خصائص إضافية حسب الدور:
                'fatherName' => 'required|string|max:255',
                'birthDate' => 'required|string',
                'phoneNumber' => 'required|digits:10',
                'address' => 'required|string',
                'studyOrCareer' => 'required|string',
                'magazeh' => 'required',
                'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

                // خصائص خاصة بكل دور:
                'PreviousCoursesInOtherPlace' => 'nullable|string',
                'isPreviousStudent' => 'nullable|boolean',
                'previousCourses' => 'nullable|string',

                'PreviousExperience' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
            }

            $validated = $validator->validated();

            // إنشاء مستخدم في جدول users
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
            ]);

            $imagePath = null;
            if ($request->hasFile('profile_image')) {
                $imagePath = $request->file('profile_image')->store('users_profile_images', 'public');
            }

            switch ($user->role) {
                case 'student':
                    $user->student()->create([
                        'user_id' => $user->id,
                        'fatherName' => $validated['fatherName'],
                        'phoneNumber' => $validated['phoneNumber'],
                        'birthDate' => $validated['birthDate'],
                        'address' => $validated['address'],
                        'studyOrCareer' => $validated['studyOrCareer'],
                        'magazeh' => $validated['magazeh'],
                        'PreviousCoursesInOtherPlace' => $validated['PreviousCoursesInOtherPlace'] ?? '',
                        'isPreviousStudent' => $validated['isPreviousStudent'] ?? false,
                        'previousCourses' => $validated['previousCourses'] ?? null,
                    ]);
                    break;

                case 'supervisor':
                    $user->supervisor()->create([
                        'user_id' => $user->id,
                        'fatherName' => $validated['fatherName'],
                        'phoneNumber' => $validated['phoneNumber'],
                        'birthDate' => $validated['birthDate'],
                        'address' => $validated['address'],
                        'studyOrCareer' => $validated['studyOrCareer'],
                        'magazeh' => $validated['magazeh'],
                        'PreviousExperience' => $validated['PreviousExperience'] ?? null,
                    ]);
                    break;
                case 'teacher':
                    $user->teacher()->create([]);
            }

            $token = $user->createToken($user->role . '-token')->plainTextToken;

            return response()->json([
                'message' => 'User registered successfully',
                'role' => $user->role,
                'user_id' => $user->id,
                'token' => $token,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
