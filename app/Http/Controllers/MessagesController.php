<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessagesController extends Controller
{
    // تابع بجيب كل البريد الوارد
    // تابع يجيب كل البريد الصادر
    // تابع بجيب المشرفين
    // تابع بجيب لفلات الاستاذ
    // تابع بجيب الطلاب للأستاذ
    // تابع ارسال رسالة


    public function getSubadmin() {
        $subadmins = DB::table('subadmins')
            ->join('users', 'subadmins.userID', '=', 'users.id')
            ->select([
                'users.id as id',
                'users.role',
                'users.firstAndLastName as firstAndLastName'
            ])
            ->get();

        return response()->json([
            'subadmin' => $subadmins
        ]);
    }

    public function getLevelsForTeacher(){
        $user = Auth::user();
        $levels = DB::table('levels')
                    ->join('subjects', 'subjects.levelID', '=', 'levels.id')
                    ->join('courses', 'courses.id', '=', 'levels.courseID')
                    ->where('courses.status', 'current')
                    ->where('subjects.teacherID', $user->teacher->id)
                    ->select(['levels.id', 'levels.levelName'])
                    ->get();

        return response()->json([
            'levels' => $levels
        ]);
    }

    public function getStudentInLevel($levelID){

        $students = DB::table('level_student_pivot')
            ->join('students', 'level_student_pivot.studentID', '=', 'students.id')
            ->join('users', 'students.userID', '=', 'users.id')
            ->where('level_student_pivot.levelID', $levelID)
            ->select([
                'students.id as studentID',
                'users.firstAndLastName as firstAndLastName'
            ])
            ->get();

        return response()->json([
            'students' => $students
        ]);

    }


    public function sendMessage(Request $request) {

        $user = Auth::user();

        $validated = $request->validate([
            'receiverID' => ['required', 'string', 'exists:users,id'],
            'subject' => ['required', 'string'],
            'content' => ['required', 'string'],
        ]);

        $message = Message::create([
            'senderID' => $user->id,
            'receiverID' => $validated['receiverID'],
            'content' => $validated['content'],
            'subject' => $validated['subject'],
        ]);

        return response()->json([
            'message' => 'Message sent successfully'
        ], 201);

    }


    public function receivedMessages() {

        $user = Auth::user();

        $message = Message::where('receiverID', $user->id)
            ->with(['sender'])
            ->latest()
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'from' => $message->sender->firstAndLastName ,
                    'senderRole' => $message->sender->role ,
                    'receivedAt' => $message->created_at->format('Y-m-d H:i'),
                    'subject' => $message->subject,
                    'content' => $message->content,
                    'open' => $message->open,
                ];
            });

        return response()->json(['received' => $message], 200);
    }


    public function sentMessages() {

        $user = Auth::user();

        $messages = Message::where('senderID', $user->id)
            ->latest()
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'to' => $message->receiver->firstAndLastName ,
                    'receiverRole' => $message->receiver->role ,
                    'sentAt' => $message->created_at->format('Y-m-d H:i'),
                    'subject' => $message->subject,
                    'content' => $message->content,
                ];
            });

        return response()->json(['sent' => $messages], 200);
    }


    public function openMessage($messageID) {
        $message = Message::find($messageID);
        if($message == null){
            return response()->json([
                'message' => 'Message not found!'
            ], 404);
        }

        if($message->open == false){
            $message->update([
                'open' => true
            ]);

            return response()->json([
                'message' => 'message change its open successfully',
                'data' => $message->fresh()
            ], 200);
        }else
        return response()->json([
                'message' => 'message is already opened',
                'data' => $message->open
        ], 422);
    }


}
