<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMember;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * 🟢 إنشاء محادثة جديدة وإضافة عضو مباشرة
     */
    public function createConversation(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $conversation = ChatConversation::create([
            'created_at' => now(),
        ]);

        ChatMember::create([
            'conversation_id' => $conversation->id,
            'user_id' => Auth::id(),
        ]);

        ChatMember::create([
            'conversation_id' => $conversation->id,
            'user_id' => $request->user_id,
        ]);

        return response()->json([
            'message' => 'Conversation created successfully with member ✅',
            'conversation' => $conversation
        ], 201);
    }

    /**
     * 💬 إرسال رسالة (دعم التشفير أو النص العادي)
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:chat_conversations,id',
            'body' => 'required|string',
            'attachment_url' => 'nullable|string'
        ]);

        $conversationId = $request->conversation_id;
        $senderId = Auth::id();

        $isMember = ChatMember::where('conversation_id', $conversationId)
            ->where('user_id', $senderId)
            ->exists();

        if (!$isMember) {
            return response()->json(['message' => 'You are not a member ❌'], 403);
        }

        $members = ChatMember::where('conversation_id', $conversationId)->get();
        $originalText = $request->body;
        $encryptedPayload = [];

        foreach ($members as $member) {
            $recipient = User::find($member->user_id);
            
            if ($recipient && $recipient->public_key) {
                $publicKey = openssl_pkey_get_public($recipient->public_key);
                if ($publicKey) {
                    openssl_public_encrypt($originalText, $encrypted, $publicKey);
                    $encryptedPayload[$recipient->id] = base64_encode($encrypted);
                    continue; 
                }
            }

            $encryptedPayload[$recipient->id] = $originalText;
        }

        $message = ChatMessage::create([
            'conversation_id' => $conversationId,
            'sender_user_id'  => $senderId,
            'body'            => json_encode($encryptedPayload),
            'attachment_url'  => $request->attachment_url,
            'sent_at'         => now(),
        ]);

        return response()->json([
            'message' => 'Message sent successfully ✅',
            'data'    => $message
        ], 201);
    }

    /**
     * 📥 جلب الرسائل غير المقروءة للمريض (بناءً على التوكن)
     */
    public function getPatientUnseenMessages()
    {
        $userId = Auth::id();

        $unseenMessages = ChatMessage::whereHas('conversation.members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->whereNull('seen_at') 
        ->where('sender_user_id', '!=', $userId)
        ->with(['sender', 'conversation'])
        ->orderBy('sent_at', 'desc')
        ->get();

        return response()->json([
            'count' => $unseenMessages->count(),
            'messages' => $unseenMessages
        ]);
    }

    /**
     * 📥 جلب الرسائل غير المقروءة للطبيب (بناءً على التوكن)
     */
    public function getDoctorUnseenMessages()
    {
        $userId = Auth::id();

        $unseenMessages = ChatMessage::whereHas('conversation.members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->whereNull('seen_at') 
        ->where('sender_user_id', '!=', $userId)
        ->with(['sender', 'conversation'])
        ->orderBy('sent_at', 'desc')
        ->get();

        return response()->json([
            'count' => $unseenMessages->count(),
            'messages' => $unseenMessages
        ]);
    }

    public function myConversations()
    {
        $conversations = ChatConversation::whereHas('members', function($q){
            $q->where('user_id', Auth::id());
        })
        ->with(['members.user'])
        ->get();

        return response()->json(['conversations' => $conversations]);
    }

    public function getMessages($conversationId)
    {
        $userId = Auth::id();
        $messages = ChatMessage::where('conversation_id', $conversationId)
            ->with('sender')
            ->orderBy('sent_at', 'asc')
            ->get();

        $messages = $messages->map(function ($msg) use ($userId) {
            $payload = json_decode($msg->body, true);
            $msg->body = $payload[$userId] ?? null;
            return $msg;
        });

        return response()->json(['messages' => $messages]);
    }

    public function markAsSeen($messageId)
    {
        $message = ChatMessage::findOrFail($messageId);
        $message->seen_at = now();
        $message->save();

        return response()->json(['message' => 'Message marked as seen ✅']);
    }
}