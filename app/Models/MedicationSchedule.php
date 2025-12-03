<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationSchedule extends Model
{
    use HasFactory;

    protected $table = 'medication_schedules';
    public $timestamps = false;

    protected $fillable = [
        'patient_id',
        'medication_id',
        'start_date',
        'end_date',
        'times_per_day',
        'times_of_day',
        'created_at',
    ];

    protected $casts = [
        'times_of_day' => 'array',
    ];

    // 👤 مريض
    public function patient()
    {
        return $this->belongsTo(\App\Models\PatientProfile::class, 'patient_id');
    }

    // 💊 الدواء
    public function medication()
    {
        return $this->belongsTo(\App\Models\Medication::class, 'medication_id');
    }

    // 🕒 الجرعات اليومية
    public function intakes()
    {
        return $this->hasMany(\App\Models\MedicationIntake::class, 'schedule_id');
    }
}
