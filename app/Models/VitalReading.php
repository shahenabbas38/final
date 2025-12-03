<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VitalReading extends Model
{
    use HasFactory;

    protected $table = 'vital_readings';
    public $timestamps = false;

    protected $fillable = [
        'patient_id',
        'type',
        'value',
        'aux_value',
        'measured_at',
        'source',
        'note'
    ];

    // 👩‍🦰 العلاقة مع المريض
    public function patient()
    {
        return $this->belongsTo(\App\Models\PatientProfile::class, 'patient_id');
    }
}
