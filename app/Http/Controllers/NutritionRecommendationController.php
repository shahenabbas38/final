<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NutritionRecommendation;
use App\Models\PatientProfile;

class NutritionRecommendationController extends Controller
{
    // 📥 عرض معلومات المريض + التوصيات
    public function index(Request $request)
    {
        $patientId = $request->user()->id;

        // 🧑‍⚕️ جلب معلومات المريض
        $patient = PatientProfile::where('user_id', $patientId)->first();

        if (!$patient) {
            return response()->json([
                'message' => '⚠️ Patient profile not found'
            ], 404);
        }

        // 📊 جلب التوصيات مرتبة حسب نوع الوجبة
        $recommendations = NutritionRecommendation::where('patient_id', $patientId)
            ->orderByRaw("FIELD(meal_type, 'BREAKFAST', 'LUNCH', 'DINNER')")
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Recommendations fetched successfully ✅',
            'patient' => [
                'id' => $patient->user_id,
                'full_name' => $patient->full_name,
                'gender' => $patient->gender,
                'dob' => $patient->dob,
                'height_cm' => $patient->height_cm,
                'weight_kg' => $patient->weight_kg,
                'primary_condition' => $patient->primary_condition,
                'address' => $patient->address,
                'emergency_contact' => $patient->emergency_contact,
                'avatar_url' => $patient->avatar_url,
            ],
            'recommendations' => $recommendations
        ]);
    }

    // ➕ إضافة توصيات جديدة (من الذكاء الاصطناعي)
    public function store(Request $request)
    {
        $request->validate([
            'recommendations' => 'required|array'
        ]);

        foreach ($request->recommendations as $rec) {
            NutritionRecommendation::create([
                'patient_id'    => $request->user()->id,
                'food_name'     => $rec['food_name'],
                'calories'      => $rec['calories'],
                'protein'       => $rec['protein'],
                'carbohydrates' => $rec['carbohydrates'],
                'fat'           => $rec['fat'],
                'description'   => $rec['description'] ?? '',
                'confidence'    => $rec['confidence'] ?? 0,
                'meal_type'     => $rec['meal_type'] ?? null, // ✅ دعم meal_type
            ]);
        }

        return response()->json([
            'message' => 'Recommendations saved successfully ✅'
        ], 201);
    }
}
