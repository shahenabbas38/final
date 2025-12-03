<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $table = 'medical_records';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'visit_date',
        'notes',
        'assessment',
        'plan',
    ];

    /**
     * 🧍‍♂️ المريض المرتبط بالسجل
     */
    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id', 'user_id');
    }

    /**
     * 🧑‍⚕️ الطبيب المرتبط بالسجل
     */
    public function doctor()
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id', 'user_id');
    }

    /**
     * 🧪 التشخيصات المرتبطة بهذا السجل
     */
    public function diagnoses()
    {
        return $this->hasMany(Diagnosis::class, 'medical_record_id');
    }
}
