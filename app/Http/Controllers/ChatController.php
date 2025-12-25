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
        // تم التعديل لطلب user_id بدلاً من إنشاء محادثة فارغة
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $conversation = ChatConversation::create([
            'created_at' => now(),
        ]);

        // إضافة المنشئ
        ChatMember::create([
            'conversation_id' => $conversation->id,
            'user_id' => Auth::id(),
        ]);

        // إضافة الطرف الآخر مباشرة (توفيراً للوقت والطلبات)
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
            
            // قمنا بتعديل المنطق هنا:
            // 1. إذا وجد مفتاح تشفير، يتم التشفير
            if ($recipient && $recipient->public_key) {
                $publicKey = openssl_pkey_get_public($recipient->public_key);
                if ($publicKey) {
                    openssl_public_encrypt($originalText, $encrypted, $publicKey);
                    $encryptedPayload[$recipient->id] = base64_encode($encrypted);
                    continue; // ننتقل للعضو التالي
                }
            }

            // 2. إذا لم يوجد مفتاح، يتم تخزين النص العادي (هذا يحل مشكلة الـ null)
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

    // ... باقي الدوال (myConversations, getMessages, markAsSeen) تبقى كما هي لأن منطقها سليم
    
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
            // سيتم إرجاع النص (سواء كان مشفراً أو عادياً) بدلاً من null
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