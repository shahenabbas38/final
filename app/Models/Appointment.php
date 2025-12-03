<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointments';

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'clinic_id',
        'start_at',
        'end_at',
        'status',
        'reason',
        'created_by_user_id',
    ];

    /* ============================
       🔗 العلاقات (Relations)
       ============================ */

    // الطبيب صاحب الموعد
    public function doctor()
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id', 'user_id');
    }

    // المريض الذي حجز الموعد
    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id', 'user_id');
    }

    // العيادة
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    // المستخدم الذي أنشأ الموعد (قد يكون الطبيب أو المريض)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
