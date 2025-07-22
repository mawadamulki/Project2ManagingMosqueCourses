<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Models\Admin;
use App\Http\Models\Student;
use App\Http\Models\Teacher;
use App\Http\Middleware\role;
use App\Http\Models\Subadmin;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        return DB::transaction(function () use ($request) {
        $data = $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'role' => 'required|in:admin,teacher,student,subadmin',
            'firstAndLastName' => 'required|string',
            'fatherName' => 'required|string',
            'birthDate' => 'required|string',
            'address' => 'required|string',
            'phoneNumber' => 'required|string',

            // Teacher-specific validation
            'studyOrCareer' => 'required_if:role,teacher',
            'magazeh' => 'required_if:role,teacher|boolean',
            'PreviousExperience' => 'required_if:role,teacher',

            // Student-specific validation
            'studyOrCareer' => 'required_if:role,student',
            'magazeh' => 'required_if:role,student|boolean',
            'PreviousCoursesInOtherPlace' => 'required_if:role,student',
            'isPreviousStudent' => 'required_if:role,student|boolean',

            // Subadmin-specific validation
            'studyOrCareer' => 'required_if:role,subadmin',
            'magazeh' => 'required_if:role,subadmin|boolean',
            'PreviousExperience' => 'required_if:role,subadmin',
        ]);

        // Create base user
        $user = User::create([
            'firstAndLastName' => $data['firstAndLastName'],
            'fatherName' => $data['fatherName'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'phoneNumber' => $data['phoneNumber'],
            'address' => $data['address'],
            'birthDate' => $data['birthDate'],
        ]);

        // Create role-specific record
        try {
            switch ($data['role']) {
                case 'teacher':
                    $user->teacher()->create([
                        'studyOrCareer' => $data['studyOrCareer'],
                        'magazeh' => filter_var($data['magazeh'], FILTER_VALIDATE_BOOLEAN),
                        'PreviousExperience' => $data['PreviousExperience']
                    ]);
                    break;

                case 'student':
                    $studentData = [
                        'studyOrCareer' => $data['studyOrCareer'],
                        'magazeh' => filter_var($data['magazeh'], FILTER_VALIDATE_BOOLEAN),
                        'PreviousCoursesInOtherPlace' => $data['PreviousCoursesInOtherPlace'],
                        'isPreviousStudent' => filter_var($data['isPreviousStudent'], FILTER_VALIDATE_BOOLEAN),
                    ];

                    // Only add previousCourses if it exists in the data
                    if (isset($data['previousCourses'])) {
                        $studentData['previousCourses'] = $data['previousCourses'];
                    }

                    $user->student()->create($studentData);
                    break;


                case 'subadmin':
                    $user->subadmin()->create([
                        'studyOrCareer' => $data['studyOrCareer'],
                        'magazeh' => filter_var($data['magazeh'], FILTER_VALIDATE_BOOLEAN),
                        'PreviousExperience' => $data['PreviousExperience']
                    ]);
                    break;

                case 'admin':
                    $user->admin()->create();
                    break;
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Registration successful',
                'access_token' => $token
            ], 201);

        } catch (\Exception $e) {

            throw $e;
        }

        });
    }



    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->firstAndLastName,
                'email' => $user->email,
                'role' => $user->role
            ]
        ]);
    }


    public function logout()
    {

        $user = Auth::user();
        // $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }



    public function user()
    {
        $user = Auth::user();
        return response()->json([
            'user' => $user,
        ]);
    }


}









