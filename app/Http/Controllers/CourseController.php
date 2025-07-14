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

    public function createCourse(Request $request)
    {
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

    // public function updateCourseByAdmin(Request $request, $id)
    // {
    //     $user = Auth::user();

    //     if ($user && $user->role === 'admin') {
    //         $course = Course::find($id);
    //         if (!$course) {
    //             return response()->json(['message' => 'Course not found'], 404);
    //         }

    //         $validated = $request->validate([
    //             'courseName' => ['nullable', 'string'],
    //             'status' => ['nullable', 'in:previous,current,new'],
    //             'courseImage' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
    //         ]);

    //         if (isset($validated['courseName'])) $course->courseName = $validated['courseName'];
    //         if (isset($validated['status'])) $course->status = $validated['status'];

    //         if ($request->hasFile('courseImage')) {
    //             $imagePath = $request->file('courseImage')->store('courses/img', 'public');
    //             $course->courseImage = $imagePath;
    //         }

    //         $course->save();

    //         return response()->json([
    //             'message' => 'Course updated successfully.',
    //             'course' => [
    //                 'courseName' => $course->courseName,
    //                 'status' => $course->status,
    //                 'image_url' => asset('storage/' . $imagePath),
    //             ],
    //         ], 201);
    //     }

    //     return response()->json(['message' => 'User not authorized'], 403);
    // }

    public function getNewCourses(){
        $courses = Course::where('status','new')->get();

        return response()->json([
            'courses' =>$courses
        ]);
    }

    public function getCurrentCourses(){
        $courses = Course::where('status','current')->get();

        return response()->json([
            'courses' =>$courses
        ]);
    }

    public function getPreviousCourses(){
        $courses = Course::where('status','previous')->get();

        return response()->json([
            'courses' =>$courses
        ]);
    }

    public function startNewCourse($courseId){

        $course = Course::find($courseId);
        if($course == null){
            return response()->json([
                'message' => 'course not found!'
            ], 404);
        }

        if($course->status == 'new'){
            $course->update([
                'status' => 'current'
            ]);

            return response()->json([
                'message' => 'course change its status successfully',
                'course' => $course->fresh()
            ], 200);
        }else
        return response()->json([
                'message' => 'course is not new course',
                'current_status' => $course->status
        ], 422);   // 422 Unprocessable Entity

    }

    public function endCurrentCourse($courseId){

        $course = Course::find($courseId);
        if($course == null){
            return response()->json([
                'message' => 'course not found!'
            ], 404);
        }

        if($course->status == 'current'){
            $course->update([
                'status' => 'previous'
            ]);

            return response()->json([
                'message' => 'course change its status successfully',
                'course' => $course->fresh()
            ], 200);
        }else
        return response()->json([
                'message' => 'course is not current course',
                'current_status' => $course->status
        ], 422);

    }


}
