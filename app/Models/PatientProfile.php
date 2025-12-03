<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientProfile extends Model
{
    use HasFactory;

    // 🩺 اسم الجدول في قاعدة البيانات
    protected $table = 'patient_profiles';

    // 📌 المفتاح الأساسي مرتبط بـ user_id من جدول users
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';

    // ✍️ الأعمدة القابلة للتعبئة
    protected $fillable = [
        'user_id',
        'full_name',
        'gender',
        'dob',
        'height_cm',
        'weight_kg',
        'primary_condition',
        'address',
        'emergency_contact',
        'avatar_url',
    ];

    // 🕒 لو كنت تستخدم created_at و updated_at
    public $timestamps = true;

    // 🧍 العلاقة مع جدول users
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
