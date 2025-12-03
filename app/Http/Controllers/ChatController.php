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
     * 🟢 إنشاء محادثة جديدة
     */
    public function createConversation()
    {
        $conversation = ChatConversation::create([
            'created_at' => now(),
        ]);

        ChatMember::create([
            'conversation_id' => $conversation->id,
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Conversation created successfully ✅',
            'conversation' => $conversation
        ], 201);
    }

    /**
     * 👥 إضافة عضو جديد لمحادثة
     */
    public function addMember(Request $request, $conversationId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        ChatMember::firstOrCreate([
            'conversation_id' => $conversationId,
            'user_id' => $request->user_id,
        ]);

        return response()->json(['message' => 'Member added successfully ✅']);
    }

    /**
     * 💬 إرسال رسالة (مع تشفير تلقائي)
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

        // ✅ تحقق أن المستخدم عضو في المحادثة
        $isMember = ChatMember::where('conversation_id', $conversationId)
            ->where('user_id', $senderId)
            ->exists();

        if (!$isMember) {
            return response()->json(['message' => 'You are not a member of this conversation ❌'], 403);
        }

        // 📥 اجلب كل أعضاء المحادثة
        $members = ChatMember::where('conversation_id', $conversationId)->get();

        $originalText = $request->body;
        $encryptedPayload = [];

        // 🛡️ تشفير الرسالة لكل مستخدم (حتى المرسل نفسه)
        foreach ($members as $member) {
            $recipient = User::find($member->user_id);
            if (!$recipient || !$recipient->public_key) continue;

            $publicKey = openssl_pkey_get_public($recipient->public_key);

            if ($publicKey) {
                openssl_public_encrypt($originalText, $encrypted, $publicKey);
                $encryptedPayload[$recipient->id] = base64_encode($encrypted);
            }
        }

        // 📝 تخزين الرسالة المشفرة بشكل JSON (مشفرة لكل مستلم)
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
     * 📜 عرض محادثات المستخدم
     */
    public function myConversations()
    {
        $conversations = ChatConversation::whereHas('members', function($q){
            $q->where('user_id', Auth::id());
        })
        ->with(['members.user'])
        ->get();

        return response()->json(['conversations' => $conversations]);
    }

    /**
     * 📨 عرض الرسائل (مشفّرة — لا يفك السيرفر التشفير)
     */
    public function getMessages($conversationId)
    {
        $userId = Auth::id();
        $messages = ChatMessage::where('conversation_id', $conversationId)
            ->with('sender')
            ->orderBy('sent_at', 'asc')
            ->get();

        // 🧠 إرجاع فقط الرسالة المشفرة الخاصة بالمستخدم الحالي
        $messages = $messages->map(function ($msg) use ($userId) {
            $payload = json_decode($msg->body, true);
            $msg->body = $payload[$userId] ?? null;
            return $msg;
        });

        return response()->json(['messages' => $messages]);
    }

    /**
     * 👁️ تعليم رسالة كمقروءة
     */
    public function markAsSeen($messageId)
    {
        $message = ChatMessage::findOrFail($messageId);
        $message->seen_at = now();
        $message->save();

        return response()->json(['message' => 'Message marked as seen ✅']);
    }
}
