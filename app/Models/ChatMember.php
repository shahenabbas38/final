<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMember extends Model
{
    use HasFactory;

    protected $table = 'chat_members';
    public $timestamps = false;

    protected $fillable = [
        'conversation_id',
        'user_id',
    ];

    // 🔹 العضو ينتمي لمحادثة
    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    // 🔹 العضو مرتبط بمستخدم
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
