<?php

namespace App\Http\Controllers;

use App\Models\NutritionRecommendation;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class NutritionRecommendationController extends Controller
{
    public function store(Request $request)
    {
        // 1. جلب المستخدم من التوكن (Bearer Token)
        $user = Auth::user();
        
        // 2. جلب بروفايل المريض مع التأكد من وجود البيانات الأساسية
        $profile = PatientProfile::where('user_id', $user->id)->first();
        
        if (!$profile) {
            return response()->json(['message' => 'بروفايل المريض غير موجود، يرجى إكمال بياناتك أولاً'], 404);
        }

        // تحقق بسيط لضمان عدم إرسال قيم فارغة للبايثون قد تسبب خطأ
        if (!$profile->weight_kg || !$profile->height_cm || !$profile->dob) {
            return response()->json(['message' => 'بيانات الوزن أو الطول أو تاريخ الميلاد ناقصة في ملفك الشخصي'], 422);
        }

        // 3. رابط خدمة البايثون (تأكد من استبداله برابط Railway الفعلي)
        $pythonApiUrl = "https://python-production-xxxx.up.railway.app/recommend";

        try {
            // 4. إرسال البيانات (المستخدم لا يرسل شيئاً، الـ Laravel هو من يجهز الـ Body)
            $response = Http::timeout(60)->post($pythonApiUrl, [
                'full_name'         => $profile->full_name,
                'weight_kg'         => (float) $profile->weight_kg,
                'height_cm'         => (float) $profile->height_cm,
                'gender'            => $profile->gender,
                'dob'               => $profile->dob,
                'primary_condition' => $profile->primary_condition ?? 'NONE',
            ]);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'محرك الذكاء الاصطناعي لا يستجيب حالياً',
                    'details' => $response->body()
                ], 500);
            }

            $result = $response->json();

            // 5. حفظ التوصيات في قاعدة البيانات لغايات الأرشفة والعرض لاحقاً
            foreach (['breakfast', 'lunch', 'dinner'] as $mealCategory) {
                if (isset($result['meals'][$mealCategory])) {
                    foreach ($result['meals'][$mealCategory] as $meal) {
                        NutritionRecommendation::create([
                            'patient_id'    => $user->id,
                            'food_name'     => $meal['food_name'],
                            'meal_type'     => $meal['meal_type'],
                            'calories'      => $meal['calories'] ?? 0,
                            'protein'       => $meal['protein'] ?? 0,
                            'carbohydrates' => $meal['carbohydrates'] ?? 0,
                            'fat'           => $meal['fat'] ?? 0,
                            'description'   => "وجبة مقترحة بناءً على تحليل الحالة الصحية",
                            'confidence'    => 0.95,
                            'created_at'    => now()
                        ]);
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'patient' => $user->full_name,
                'daily_calories_needed' => $result['patient_info']['daily_calories'] ?? null,
                'recommendations' => $result['meals']
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'حدث خطأ غير متوقع',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}