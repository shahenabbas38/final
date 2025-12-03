<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    use HasFactory;

    protected $table = 'chat_conversations';
    public $timestamps = false; // لأن الجدول فيه فقط created_at

    protected $fillable = [
        'created_at',
    ];

    // 🔹 كل محادثة تحتوي على أعضاء
    public function members()
    {
        return $this->hasMany(ChatMember::class, 'conversation_id');
    }

    // 🔹 كل محادثة تحتوي على رسائل
    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }
}
