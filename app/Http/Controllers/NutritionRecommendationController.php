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
        
        // 1. جلب بروفايل المريض كامل ببياناته الشخصية
        $profile = PatientProfile::where('user_id', $user->id)->first();
        
        if (!$profile) {
            return response()->json(['message' => 'بروفايل المريض غير موجود في النظام'], 404);
        }

        // 2. تحضير كافة البيانات الشخصية لإرسالها لموديل الذكاء الاصطناعي
        $patientParams = [
            'full_name'         => $profile->full_name,
            'weight_kg'         => (float) $profile->weight_kg,
            'height_cm'         => (float) $profile->height_cm,
            'gender'            => $profile->gender,
            'primary_condition' => $profile->primary_condition,
            'dob'               => $profile->dob, // تاريخ الميلاد لحساب العمر إن لزم
        ];

        // 3. استدعاء سكريبت البايثون
        $pythonPath = base_path('food_recommendation.py');
        $command = "python3 " . escapeshellarg($pythonPath) . " " . escapeshellarg(json_encode($patientParams)) . " 2>&1";
        $output = shell_exec($command);
        $result = json_decode($output, true);

        // 4. التحقق من وجود أخطاء في السكريبت
        if (!$result || isset($result['error'])) {
            return response()->json([
                'error' => 'خطأ في معالجة البيانات من قبل الذكاء الاصطناعي',
                'details' => $result['error'] ?? 'No output from script'
            ], 500);
        }

        // 5. حفظ الـ 15 وجبة الناتجة في قاعدة البيانات مع ربطها بالمريض
        foreach (['breakfast', 'lunch', 'dinner'] as $mealCategory) {
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

        // 6. الرد النهائي لـ Flutter (يحتوي على بيانات المريض الشخصية + التوصيات)
        return response()->json([
            'status' => 'success',
            'patient_profile' => $result['patient_info'], // البيانات الشخصية التي حسبها الموديل
            'daily_plan' => [
                'breakfast' => $result['breakfast'],
                'lunch'     => $result['lunch'],
                'dinner'    => $result['dinner']
            ]
        ], 201);
    }
}