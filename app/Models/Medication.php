<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
    use HasFactory;

    protected $table = 'medications';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'form',
        'strength',
        'unit'
    ];

    // 🧾 كل دواء ممكن يكون ضمن عدة وصفات (عبر prescription_items)
    public function prescriptionItems()
    {
        return $this->hasMany(\App\Models\PrescriptionItem::class, 'medication_id');
    }
}
