<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model
{
    use HasFactory;

    // 🕒 تعطيل الـ timestamps لأن الجدول ما فيه created_at / updated_at
    public $timestamps = false;

    protected $table = 'diagnoses';

    protected $fillable = [
        'medical_record_id',
        'code',
        'label',
        'severity',
    ];

    /**
     * 🧬 العلاقة مع السجل الطبي (Many diagnoses to One medical record)
     */
    public function medicalRecord()
    {
        return $this->belongsTo(\App\Models\MedicalRecord::class, 'medical_record_id');
    }

    /**
     * 🧑‍⚕️ الطبيب صاحب التشخيص (عن طريق medical record)
     */
    public function doctor()
    {
        return $this->hasOneThrough(
            \App\Models\DoctorProfile::class,
            \App\Models\MedicalRecord::class,
            'id',                // local key in medical_records
            'user_id',           // foreign key in doctor_profiles
            'medical_record_id', // foreign key in diagnoses
            'doctor_id'          // foreign key in medical_records
        );
    }

    /**
     * 🧍 المريض صاحب التشخيص (عن طريق medical record)
     */
    public function patient()
    {
        return $this->hasOneThrough(
            \App\Models\PatientProfile::class,
            \App\Models\MedicalRecord::class,
            'id',                // local key in medical_records
            'user_id',           // foreign key in patient_profiles
            'medical_record_id', // foreign key in diagnoses
            'patient_id'         // foreign key in medical_records
        );
    }
}
