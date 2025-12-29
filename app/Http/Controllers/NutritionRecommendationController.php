<?php

namespace App\Http\Controllers;

use App\Models\NutritionRecommendation;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NutritionRecommendationController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // 1. جلب بروفايل المريض
        $profile = PatientProfile::where('user_id', $user->id)->first();
        
        if (!$profile) {
            return response()->json(['message' => 'بروفايل المريض غير موجود في النظام'], 404);
        }

        // 2. تحضير البيانات الشخصية
        $patientParams = [
            'full_name'         => $profile->full_name,
            'weight_kg'         => (float) $profile->weight_kg,
            'height_cm'         => (float) $profile->height_cm,
            'gender'            => $profile->gender,
            'primary_condition' => $profile->primary_condition,
            'dob'               => $profile->dob,
        ];

        // 3. تحديث المسار ليشمل مجلد ai
        // base_path يعطينا المسار الرئيسي، ثم نضيف ai والملف
        $pythonPath = base_path('ai/food_recommendation.py');
        
        if (!file_exists($pythonPath)) {
            return response()->json([
                'error' => 'الملف غير موجود في مجلد ai',
                'checked_path' => $pythonPath
            ], 500);
        }

        // 4. تنفيذ الأمر
        $jsonParams = json_encode($patientParams);
        // نستخدم python3 للتوافق مع Railway
        $command = "python " . escapeshellarg($pythonPath) . " " . escapeshellarg($jsonParams) . " 2>&1";
        
        $output = shell_exec($command);
        $result = json_decode($output, true);

        // 5. فحص النتيجة ومعالجة الخطأ بصيغة واضحة
        if (is_null($result)) {
            return response()->json([
                'error' => 'فشل تشغيل الذكاء الاصطناعي',
                'debug_info' => $output, // هنا سيظهر أي خطأ داخلي في بايثون
                'path_used' => $pythonPath
            ], 500);
        }

        if (isset($result['error'])) {
            return response()->json(['error' => 'خطأ من البايثون', 'details' => $result['error']], 500);
        }

        // 6. حفظ الوجبات (15 وجبة)
        foreach (['breakfast', 'lunch', 'dinner'] as $mealCategory) {
            if (isset($result[$mealCategory])) {
                foreach ($result[$mealCategory] as $meal) {
                    NutritionRecommendation::create([
                        'patient_id'    => $user->id,
                        'food_name'     => $meal['food_name'],
                        'meal_type'     => $meal['meal_type'],
                        'calories'      => $meal['calories'],
                        'protein'       => $meal['protein'],
                        'carbohydrates' => $meal['carbohydrates'],
                        'fat'           => $meal['fat'],
                        'description'   => $meal['description'],
                        'confidence'    => $meal['confidence'],
                        'created_at'    => now()
                    ]);
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'patient_profile' => $result['patient_info'],
            'recommendations' => [
                'breakfast' => $result['breakfast'],
                'lunch'     => $result['lunch'],
                'dinner'    => $result['dinner']
            ]
        ], 201);
    }
}