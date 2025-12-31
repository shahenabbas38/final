<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentReminder extends Model
{
    use HasFactory;

    protected $table = 'appointment_reminders';

    /**
     * 💡 حل المشكلة:
     * أخبر لارافيل أن الجدول لا يحتوي على عمود updated_at
     * أو قم بتعطيل الـ timestamps بالكامل إذا كنت تدير الوقت يدوياً
     */
    const UPDATED_AT = null; 

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
     * 🧍 العلاقة مع المستخدم عبر الموعد
     */
    public function user()
    {
        // تم التعديل لضمان الوصول للعلاقة بشكل صحيح من خلال الموعد
        return $this->appointment ? $this->appointment->patient : null;
    }
}