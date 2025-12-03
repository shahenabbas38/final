<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';
    public $timestamps = false;

    protected $fillable = [
        'conversation_id',
        'sender_user_id',
        'body',
        'attachment_url',
        'sent_at',
        'seen_at',
    ];

    // 🔹 الرسالة تنتمي إلى محادثة
    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    // 🔹 المرسل هو مستخدم
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
