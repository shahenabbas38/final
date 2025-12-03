<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTest extends Model
{
    use HasFactory;

    protected $table = 'lab_tests';
    public $timestamps = false;

    protected $fillable = [
        'patient_id',
        'ordered_by_doctor_id',
        'test_type',
        'status',
        'lab_name',
        'ordered_at',
        'due_at'
    ];

    // 🧑‍⚕️ الطبيب الذي طلب التحليل
    public function doctor()
    {
        return $this->belongsTo(\App\Models\DoctorProfile::class, 'ordered_by_doctor_id');
    }

    // 🧍 المريض
    public function patient()
    {
        return $this->belongsTo(\App\Models\PatientProfile::class, 'patient_id');
    }

    // 📊 النتائج المرتبطة
    public function results()
    {
        return $this->hasMany(\App\Models\LabResult::class, 'lab_test_id');
    }
}
