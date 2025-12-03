<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    use HasFactory;

    protected $table = 'prescription_items';
    public $timestamps = false;

    protected $fillable = [
        'prescription_id',
        'medication_id',
        'dose_amount',
        'frequency',
        'route',
        'instructions'
    ];

    public function prescription()
    {
        return $this->belongsTo(\App\Models\Prescription::class, 'prescription_id');
    }

    public function medication()
    {
        return $this->belongsTo(\App\Models\Medication::class, 'medication_id');
    }
}
