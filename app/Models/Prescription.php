<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $table = 'prescriptions';
    public $timestamps = false;

    protected $fillable = [
        'medical_record_id',
        'start_date',
        'end_date',
        'notes',
        'created_at',
    ];

    // 🩻 كل وصفة مرتبطة بسجل طبي
    public function medicalRecord()
    {
        return $this->belongsTo(\App\Models\MedicalRecord::class, 'medical_record_id');
    }

    // 💊 تحتوي على عدة عناصر أدوية
    public function items()
    {
        return $this->hasMany(\App\Models\PrescriptionItem::class, 'prescription_id');
    }
}
