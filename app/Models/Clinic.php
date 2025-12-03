<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    protected $table = 'clinics';

    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'timezone',
        'phone',
    ];

    public function doctors()
    {
        return $this->hasMany(DoctorProfile::class, 'clinic_id');
    }
}
