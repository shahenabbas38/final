<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Notification;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'email',
        'phone',
        'password_hash',
        'role',
        'status',
        'public_key',   // ✅ تمت إضافته لتخزين المفتاح العام
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
        // ⚠️ لا يوجد private_key في قاعدة البيانات — المفتاح الخاص لا يتم تخزينه
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * ✨ تحديد أن كلمة المرور موجودة في الحقل password_hash
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * 🩺 علاقة المستخدم مع ملف الطبيب
     */
    public function doctorProfile()
    {
        return $this->hasOne(DoctorProfile::class, 'user_id');
    }

    /**
     * 🧍‍♂️ علاقة المستخدم مع ملف المريض
     */
    public function patientProfile()
    {
        return $this->hasOne(\App\Models\PatientProfile::class, 'user_id');
    }

    /**
     * 📅 علاقة الطبيب مع المواعيد
     */
    public function doctorAppointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    /**
     * 📅 علاقة المريض مع المواعيد
     */
    public function patientAppointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id','id');
    }

    /**
     * 💬 علاقة المستخدم مع المحادثات
     */
    public function chatMemberships()
    {
        return $this->hasMany(ChatMember::class, 'user_id');
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_user_id');
    }

    /**
     * ⏰ علاقة التذكيرات الخاصة بالمريض
     */
    public function reminders()
    {
        return $this->hasManyThrough(
            AppointmentReminder::class,
            Appointment::class,
            'patient_id',     // Foreign key in appointments table
            'appointment_id', // Foreign key in reminders table
            'id',             // Local key in users table
            'id'              // Local key in appointments table
        );
    }

    /**
     * 🪙 إرجاع المفتاح العام للمستخدم
     */
    public function getPublicKey()
    {
        return $this->public_key;
    }
    /*************************************** */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
    // 📁 App\Models\User.php

public function nutritionRecommendations()
    {
        return $this->hasMany(\App\Models\NutritionRecommendation::class, 'patient_id', 'id');
    }


}
