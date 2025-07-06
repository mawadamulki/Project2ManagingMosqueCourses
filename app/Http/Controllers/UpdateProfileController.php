<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateProfileController extends Controller
{
    public function updateProfileImage(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $profile = UserProfile::where('userID', $user->id)->first();

        if (!$profile) {
            $profile = $user->userProfile()->create();
        }

        if ($request->hasFile('profile_image')) {
            if ($profile->profile_image && Storage::disk('public')->exists($profile->profile_image)) {
                Storage::disk('public')->delete($profile->profile_image);
            }

            $roleFolder = match ($user->role) {
                'student' => 'students',
                'teacher' => 'teachers',
                'subadmin' => 'subadmins',
                'admin' => 'admins',
            };

            $imagePath = $request->file('profile_image')->store("img/$roleFolder", 'public');

            $profile->profile_image = $imagePath;
            $profile->save();

            return response()->json([
                'message' => 'Profile image updated successfully',
                'image_url' => asset("storage/" . $imagePath)
            ]);
        }

        return response()->json(['message' => 'No image file found in request'], 400);
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        if (Hash::check($request->currentPassword, $user->password)) {
            $user->password = Hash::make($request->newPassword);
            $user->save();
            return response()->json([
                'Password updated successfully'
            ], 200);
        }
        return response()->json([
            'Current_Password not correct'
        ], 400);
    }

    public function updateEmail(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        if ($user->email !== $request->currentEmail) {
            return response()->json([
                'Current Email not correct'
            ], 400);
        }
        $user->email = $request->newEmail;
        $user->save();
        return response()->json([
            'Email updated successfully',
            'role' => $user->role
        ], 200);
    }
    public function updatePhoneNumber(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        if ($user->phoneNumber !== $request->currentPhoneNumber) {
            return response()->json([
                'Current PhoneNumber not correct'
            ], 400);
        }

        $validated = $request->validate([
            'newPhoneNumber' => ['required', 'regex:/^[0-9]{10}$/']
        ]);
        $user->phoneNumber = $request->newPhoneNumber;
        $user->save();
        return response()->json([
            'PhoneNumber updated successfully',
            'role' => $user->role
        ], 200);
    }


    public function updateStudyOrCareer(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'studyOrCareer' => 'required|string|max:255',
        ]);

        $newValue = $request->studyOrCareer;

        switch ($user->role) {
            case 'teacher':
                if (!$user->teacher) {
                    return response()->json(['message' => 'Teacher info not found'], 404);
                }
                $user->teacher->studyOrCareer = $newValue;
                $user->teacher->save();
                break;

            case 'subadmin':
                if (!$user->subadmin) {
                    return response()->json(['message' => 'Subadmin info not found'], 404);
                }
                $user->subadmin->studyOrCareer = $newValue;
                $user->subadmin->save();
                break;

            case 'student':
                if (!$user->student) {
                    return response()->json(['message' => 'Student info not found'], 404);
                }
                $user->student->studyOrCareer = $newValue;
                $user->student->save();
                break;

            default:
                return response()->json(['message' => 'Role not supported'], 400);
        }

        return response()->json([
            'message' => 'studyOrCareer updated successfully',
            'role' => $user->role
        ], 200);
    }



    public function updateMagazeh(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'magazeh' => 'required|boolean',
        ]);

        $newValue = $request->magazeh;

        switch ($user->role) {
            case 'teacher':
                if (!$user->teacher) {
                    return response()->json(['message' => 'Teacher info not found'], 404);
                }
                $user->teacher->magazeh = $newValue;
                $user->teacher->save();
                break;

            case 'subadmin':
                if (!$user->subadmin) {
                    return response()->json(['message' => 'Subadmin info not found'], 404);
                }
                $user->subadmin->magazeh = $newValue;
                $user->subadmin->save();
                break;

            case 'student':
                if (!$user->student) {
                    return response()->json(['message' => 'Student info not found'], 404);
                }
                $user->student->magazeh = $newValue;
                $user->student->save();
                break;

            default:
                return response()->json(['message' => 'Role not supported'], 400);
        }

        return response()->json([
            'message' => 'magazeh updated successfully',
            'role' => $user->role
        ], 200);
    }
    public function updatePreviousExperience(Request $request)
    {

        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'PreviousExperience' => 'required|string',
        ]);

        $newValue = $request->PreviousExperience;

        switch ($user->role) {
            case 'teacher':
                if (!$user->teacher) {
                    return response()->json(['message' => 'Teacher info not found'], 404);
                }
                $user->teacher->PreviousExperience = $newValue;
                $user->teacher->save();
                break;

            case 'subadmin':
                if (!$user->subadmin) {
                    return response()->json(['message' => 'Subadmin info not found'], 404);
                }
                $user->subadmin->PreviousExperience = $newValue;
                $user->subadmin->save();
                break;
            default:
                return response()->json(['message' => 'Role not supported'], 400);
        }
        return response()->json([
            'message' => 'PreviousExperience updated successfully',
            'role' => $user->role
        ], 200);
    }

    public function updatePreviousCoursesInOtherPlace(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'student') {
            return response()->json(['message' => 'user not a student'], 404);
        }

        $request->validate([
            'PreviousCoursesInOtherPlace' => 'required|string',
        ]);

        $newValue = $request->PreviousCoursesInOtherPlace;
        if (!$user->student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }


        $user->student->PreviousCoursesInOtherPlace = $newValue;
        $user->student->save();
        return response()->json([
            'message' => 'PreviousCoursesInOtherPlace updated successfully',
        ], 200);
    }
    public function updatePreviousCourses(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'student') {
            return response()->json(['message' => 'user not a student'], 404);
        }

        $request->validate([
            'previousCourses' => 'required|string',
        ]);

        $newValue = $request->previousCourses;
        if (!$user->student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }


        $user->student->previousCourses = $newValue;
        $user->student->isPreviousStudent = 1;
        $user->student->save();
        return response()->json([
            'message' => 'previousCourses updated successfully',
        ], 200);
    }
}
