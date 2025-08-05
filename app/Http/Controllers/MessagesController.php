<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessagesController extends Controller
{

    public function sendMessage(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'receiverName' => ['required', 'string', 'exists:users,firstAndLastName'],
            'content' => ['required', 'string'],
        ]);

        $receiver = User::where('firstAndLastName', $validated['receiverName'])->first();

        if (!$receiver) {
            return response()->json(['message' => 'Receiver not found'], 404);
        }

        $message = Message::create([
            'senderID' => $user->id,
            'receiverID' => $receiver->id,
            'content' => $validated['content'],
            'parent_id' => null,
        ]);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => [
                'from' => $message->sender->firstAndLastName ?? 'Unknown',
                'to' => $receiver->firstAndLastName,
                'content' => $message->content,
                'sentAt' => $message->created_at->format('Y-m-d H:i:s'),
            ]
        ], 201);
    }

    public function inboxReceivdeMessages()
    {
        $user = Auth::user();

        $messages = Message::where('receiverID', $user->id)
            ->with(['sender', 'parent'])
            ->latest()
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'type' => $message->parentID ? 'reply' : 'original',
                    'from' => $message->sender->firstAndLastName ?? 'Unknown',
                    'senderRole' => $message->sender->role ?? 'Unknown',
                    'receivedAt' => $message->created_at->format('Y-m-d H:i'),
                    'content' => $message->content,
                    'inReplyTo' => $message->parent ? [
                        'id' => $message->parent->id,
                        'content' => $message->parent->content,
                        'sentAt' => $message->parent->created_at->format('Y-m-d H:i'),
                    ] : null,
                ];
            });

        return response()->json(['inbox' => $messages], 200);
    }
    public function outboxSendMessages()
    {
        $user = Auth::user();

        $messages = Message::where('senderID', $user->id)
            ->with(['receiver', 'parent'])
            ->latest()
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'type' => $message->parentID ? 'reply' : 'original',
                    'to' => $message->receiver->firstAndLastName ?? 'Unknown',
                    'receiverRole' => $message->receiver->role ?? 'Unknown',
                    'sentAt' => $message->created_at->format('Y-m-d H:i'),
                    'content' => $message->content,
                    'inReplyTo' => $message->parent ? [
                        'id' => $message->parent->id,
                        'content' => $message->parent->content,
                        'sentAt' => $message->parent->created_at->format('Y-m-d H:i'),
                    ] : null,
                ];
            });

        return response()->json(['outbox' => $messages], 200);
    }

    public function replyToMessage(Request $request, $messageId)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'replyContent' => ['required', 'string'],
        ]);

        $originalMessage = Message::find($messageId);

        if (!$originalMessage) {
            return response()->json(['message' => 'Original message not found'], 404);
        }

        if ($originalMessage->receiverID !== $user->id) {
            return response()->json(['message' => 'Unauthorized to reply'], 403);
        }

        $replyMessage = Message::create([
            'senderID' => $user->id,
            'receiverID' => $originalMessage->senderID,
            'content' => $validated['replyContent'],
            'parentID' => $originalMessage->id,
        ]);

        return response()->json([
            'message' => 'Reply sent successfully',
            'data' => [
                'to' => $replyMessage->receiver->firstAndLastName ?? 'Unknown',
                'content' => $replyMessage->content,
                'sentAt' => $replyMessage->created_at->format('Y-m-d H:i'),
                'inReplyTo' => [
                    'id' => $originalMessage->id,
                    'content' => $originalMessage->content,
                    'sentAt' => $originalMessage->created_at->format('Y-m-d H:i'),
                ]
            ]
        ], 201);
    }

    public function deleteMessage($id)
    {
        $user = Auth::user();

        $message = Message::find($id);

        if (!$message) {
            return response()->json(['message' => 'Message not found'], 404);
        }

        if ($message->senderID !== $user->id && $message->receiverID !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted successfully'], 200);
    }
    public function GetAllMessages()
    {
        $user = Auth::user();

        $messages = Message::where('senderID', $user->id)
            ->orWhere('receiverID', $user->id)
            ->with(['sender', 'receiver', 'parent'])
            ->latest()
            ->get()
            ->map(function ($message) use ($user) {
                return [
                    'id' => $message->id,
                    'direction' => $message->senderID === $user->id ? 'sent' : 'received',
                    'type' => $message->parentID ? 'reply' : 'original',
                    'from' => $message->sender->firstAndLastName ?? 'Unknown',
                    'to' => $message->receiver->firstAndLastName ?? 'Unknown',
                    'senderRole' => $message->sender->role ?? 'Unknown',
                    'receiverRole' => $message->receiver->role ?? 'Unknown',
                    'sentAt' => $message->created_at->format('Y-m-d H:i'),
                    'content' => $message->content,
                    'inReplyTo' => $message->parent ? [
                        'id' => $message->parent->id,
                        'content' => $message->parent->content,
                        'sentAt' => $message->parent->created_at->format('Y-m-d H:i'),
                    ] : null,
                ];
            });

        return response()->json(['allMessages' => $messages], 200);
    }

    public function updateMessage(Request $request, $id)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'newContent' => ['required', 'string'],
        ]);

        $message = Message::find($id);

        if (!$message) {
            return response()->json(['message' => 'Message not found'], 404);
        }

        if ($message->senderID !== $user->id) {
            return response()->json(['message' => 'Unauthorized to edit this message'], 403);
        }

        $message->content = $validated['newContent'];
        $message->save();

        return response()->json([
            'message' => 'Message updated successfully',
            'data' => [
                'id' => $message->id,
                'content' => $message->content,
                'updatedAt' => $message->updated_at->format('Y-m-d H:i'),
            ]
        ], 200);
    }
}
