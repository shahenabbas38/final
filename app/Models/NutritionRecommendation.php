<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NutritionRecommendation extends Model
{
    use HasFactory;

    protected $table = 'nutrition_recommendations';
    
    // بما أنك تدير التوقيت يدوياً
    public $timestamps = false; 

    protected $fillable = [
        'patient_id',
        'food_name',
        'calories',
        'protein',
        'carbohydrates',
        'fat',
        'description',
        'confidence',
        'meal_type',
        'created_at'
    ];

    /**
     * ✅ التعديل المطلوب هنا:
     * هذا السطر يخبر لارافل بتحويل created_at من نص إلى كائن تاريخ تلقائياً
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    // علاقة مع المريض
    public function patient()
    {
        // الربط مع ملف المريض الشخصي باستخدام user_id
        return $this->belongsTo(\App\Models\PatientProfile::class, 'patient_id', 'user_id');
    }
}