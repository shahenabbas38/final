<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentReminder extends Model
{
    use HasFactory;

    protected $table = 'appointment_reminders';

    protected $fillable = [
        'appointment_id',
        'remind_at',
        'sent_at',
        'channel',
    ];

    /**
     * 🔁 العلاقة مع جدول المواعيد
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * 🧍 العلاقة مع المستخدم عبر الموعد (اختياري)
     */
    public function user()
    {
        return $this->appointment->user();
    }
}
