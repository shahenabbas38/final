<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationIntake extends Model
{
    use HasFactory;

    protected $table = 'medication_intakes';
    public $timestamps = false;

    protected $fillable = [
        'schedule_id',
        'planned_time',
        'taken_time',
        'status',
        'dose_amount',
        'created_at'
    ];

    public function schedule()
    {
        return $this->belongsTo(\App\Models\MedicationSchedule::class, 'schedule_id');
    }
}
