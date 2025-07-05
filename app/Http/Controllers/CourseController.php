<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{

    public function createCourseByAdmin(Request $request)
    {
        $user = Auth::user();

        if ($user && $user->role === 'admin') {
            $validated = $request->validate([
                'courseName' => ['required', 'string'],
                'courseImage' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            ]);

            $imagePath = $request->file('courseImage')->store('courses', 'public');

            $fullImageUrl = asset('storage/' . $imagePath);

            $course = Course::create([
                'courseName' => $validated['courseName'],
                'status' => 'new',
                'courseImage' => $fullImageUrl,
            ]);
            $levels = ['introductory', 'level1', 'level2', 'level3', 'level4', 'level5', 'level6'];
            foreach ($levels as $levelName) {
                $course->levels()->create([
                    'levelName' => $levelName
                ]);
            }
            return response()->json([
                'message' => 'Course created successfully with 7 levels.',
                'course' => [
                    'courseName' => $course->courseName,
                    'status' => $course->status,
                    'image_url' =>  $fullImageUrl,
                ],
            ], 201);
        }

        return response()->json(['message' => 'User not authorized'], 403);
    }

    public function updateCourseByAdmin(Request $request, $id)
    {
        $user = Auth::user();

        if ($user && $user->role === 'admin') {
            $course = Course::find($id);
            if (!$course) {
                return response()->json(['message' => 'Course not found'], 404);
            }

            $validated = $request->validate([
                'courseName' => ['nullable', 'string'],
                'status' => ['nullable', 'in:previous,current,new'],
                'courseImage' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            ]);

            if (isset($validated['courseName'])) $course->courseName = $validated['courseName'];
            if (isset($validated['status'])) $course->status = $validated['status'];

            if ($request->hasFile('courseImage')) {
                $imagePath = $request->file('courseImage')->store('courses/img', 'public');
                $course->courseImage = $imagePath;
            }

            $course->save();

            return response()->json([
                'message' => 'Course updated successfully.',
                'course' => [
                    'courseName' => $course->courseName,
                    'status' => $course->status,
                    'image_url' => asset('storage/' . $imagePath),
                ],
            ], 201);
        }

        return response()->json(['message' => 'User not authorized'], 403);
    }
}
