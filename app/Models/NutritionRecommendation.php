<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NutritionRecommendation extends Model
{
    use HasFactory;

    protected $table = 'nutrition_recommendations';
    public $timestamps = false; // ✅ لأنه لا يوجد created_at و updated_at تلقائي

    protected $fillable = [
        'patient_id',
        'food_name',
        'calories',
        'protein',
        'carbohydrates',
        'fat',
        'description',
        'confidence',
        'meal_type',   // 🆕 تمت إضافته هنا
        'created_at'
    ];

    // علاقة مع المريض
    public function patient()
    {
        return $this->belongsTo(\App\Models\PatientProfile::class, 'patient_id', 'user_id');
    }
}
