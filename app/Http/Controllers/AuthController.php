<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Models\Admin;
use App\Http\Models\Student;
use App\Http\Models\Supervisor;
use App\Http\Models\Teacher;

class AuthController extends Controller
{
    public function register(Request $request){

        $data = $request->validate([

            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'role' => 'required|in:admin,teacher,student,adminster',
            'firstAndLastName' => 'required|string',
            'fatherName' => 'required|string',

            // Teacher-specific validation
            'birthDate' => 'required_if:role,teacher',
            'address' => 'required_if:role,teacher',
            'study' => 'required_if:role,teacher',
            'career' => 'required_if:role,teacher',
            'magazeh' => 'required_if:role,teacher',
            'PreviousExperience' => 'required_if:role,teacher',

            // Student-specific validation
            'birthDate' => 'required_if:role,student',
            'address' => 'required_if:role,student',
            'study' => 'required_if:role,student',
            'career' => 'required_if:role,student',
            'magazeh' => 'required_if:role,student',
            'PreviousCoursesInOtherPlace' => 'required_if:role,student',
            'isPreviousStudent' => 'required_if:role,student',
            'previousCourses' => 'required_if:role,student',

            // Supervisor-specific validation
            'birthDate' => 'required_if:role,supervisor',
            'address' => 'required_if:role,supervisor',
            'study' => 'required_if:role,supervisor',
            'career' => 'required_if:role,supervisor',
            'magazeh' => 'required_if:role,supervisor',
            'PreviousExperience' => 'required_if:role,supervisor',

        ]);

        // Create base user
        $user = User::create([
            'firstAndLastName' => $data['firstAndLastName'],
            'fatherName' => $data['fatherName'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        // Create role-specific record
        switch ($data['role']) {
            case 'teacher':
                $user->teacher()->create([
                    'birthDate' => $data['birthDate'],
                    'address' => $data['address'],
                    'study' => $data['study'],
                    'career' => $data['career'],
                    'magazeh' => $data['magazeh'],
                    'PreviousExperience' => $data['PreviousExperience']
                ]);
                break;

            case 'student':
                $user->student()->create([
                    'birthDate' => $data['birthDate'],
                    'address' => $data['address'],
                    'study' => $data['study'],
                    'career' => $data['career'],
                    'magazeh' => $data['magazeh'],
                    'PreviousCoursesInOtherPlace' => $data['PreviousCoursesInOtherPlace'],
                    'isPreviousStudent' => $data['isPreviousStudent'],
                    'previousCourses' => $data['previousCourses']
                ]);
                break;

            case 'supervisor':
                $user->teacher()->create([
                    'birthDate' => $data['birthDate'],
                    'address' => $data['address'],
                    'study' => $data['study'],
                    'career' => $data['career'],
                    'magazeh' => $data['magazeh'],
                    'PreviousExperience' => $data['PreviousExperience']
                ]);
                break;

            case 'admin':
            // Admin might not need extra fields, but you can add if needed
            $user->admin()->create();
            break;
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'access_token' => $token
        ], 201);
    }











}
