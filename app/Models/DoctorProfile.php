<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorProfile extends Model
{
    use HasFactory;

    protected $table = 'doctor_profiles';
    protected $primaryKey = 'user_id';
    public $incrementing = false; // لأن user_id ليس AUTO_INCREMENT

    protected $fillable = [
        'user_id',
        'full_name',
        'gender',
        'primary_specialty_id',
        'clinic_id',
        'license_no',
        'bio',
        'avatar_url',
        // ✅ الحقول الجديدة المضافة
        'working_days', 
        'start_time',
        'end_time',
        'shift_type',
    ];

    /**
     * 🛠️ تحويل البيانات تلقائياً (Casting)
     * يساعد هذا في التعامل مع الوقت ككائن أو تنسيق معين
     */
    protected $casts = [
        'start_time' => 'string', // أو 'datetime:H:i' حسب رغبتك في الواجهة
        'end_time'   => 'string',
    ];

    /**
     * 🔗 العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 🔗 العلاقة مع العيادة
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    /**
     * 🔗 العلاقة مع الاختصاص الأساسي
     */
    public function specialty()
    {
        return $this->belongsTo(Specialty::class, 'primary_specialty_id');
    }
}