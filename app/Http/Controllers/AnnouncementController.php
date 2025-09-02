<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function createAnnouncementCourse(Request $request)
    {

        $validated = $request->validate([
            'description'=>['required', 'string'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:10240'],
        ]);

        $path = $request->file('image')->store('announcementCourseImage', 'public');
        $fullUrl = asset('storage/' . $path);

        $announcement = Announcement::create([
            'description'=>$validated['description'],
            'image' => $fullUrl,
        ]);

        return response()->json([
            'message' => 'Announcement created successfully.',
            'image_url' => $fullUrl,
            'description'=>$announcement->description
        ], 201);
    }

    // public function createMultipleAnnouncements(Request $request){

    //     $user = Auth::user();

    //     if (!$user || $user->role !== 'admin') {
    //         return response()->json(['message' => 'Unauthorized'], 403);
    //     }

    //     $validated = $request->validate([
    //         'announcementCourseImages.*' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
    //     ]);

    //     $files = $request->file('announcementCourseImages');

    //     if (!$files || !is_array($files)) {
    //         return response()->json(['message' => 'No images were uploaded.'], 400);
    //     }
    //     $imagePaths = [];

    //     foreach ($request->file('announcementCourseImages') as $image) {
    //         $path = $image->store('announcementCourseImage', 'public');
    //         $fullUrl = asset('storage/' . $path);

    //         $announcement = Announcement::create([
    //             'announcementCourseImage' => $fullUrl,
    //         ]);

    //         $imagePaths[] = $fullUrl;
    //     }

    //     return response()->json([
    //         'message' => 'Announcements created successfully.',
    //         // 'images' => $imagePaths
    //     ],201);
    // }

    public function deleteAnnouncementCourse($id){

        $announcement = Announcement::find($id);

        if (!$announcement) {
            return response()->json(['message' => 'Announcement not found'], 404);
        }
        $imagePath = str_replace(asset('storage') . '/', '', $announcement->announcementCourseImage);

        Storage::disk('public')->delete($imagePath);

        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully.']);
    }


    public function getAllAnnouncements(){

        $announcements = Announcement::select('id', 'description', 'image')->get();

        return response()->json([
            'announcements' =>$announcements
        ]);
    }



}
