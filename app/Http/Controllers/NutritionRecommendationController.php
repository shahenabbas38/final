<?php

namespace App\Http\Controllers;

use App\Models\NutritionRecommendation;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // أضفنا هذا السطر للتعامل مع الـ API

class NutritionRecommendationController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // 1. جلب بروفايل المريض من قاعدة البيانات
        $profile = PatientProfile::where('user_id', $user->id)->first();
        
        if (!$profile) {
            return response()->json(['message' => 'بروفايل المريض غير موجود في النظام'], 404);
        }

        // 2. الرابط الجديد الخاص بك من Railway (قم بتعديله بالرابط الحقيقي)
        // لا تنسَ إضافة /recommend في نهاية الرابط
        $pythonApiUrl = "https://python-production-xxxx.up.railway.app/recommend";

        try {
            // 3. إرسال البيانات إلى خدمة البايثون عبر HTTP
            $response = Http::timeout(30)->post($pythonApiUrl, [
                'full_name'         => $profile->full_name,
                'weight_kg'         => (float) $profile->weight_kg,
                'height_cm'         => (float) $profile->height_cm,
                'gender'            => $profile->gender,
                'dob'               => $profile->dob,
                'primary_condition' => $profile->primary_condition ?? 'NONE',
            ]);

            // 4. فحص استجابة الخدمة
            if ($response->failed()) {
                return response()->json([
                    'error' => 'تعذر الاتصال بمحرك الذكاء الاصطناعي',
                    'details' => $response->body()
                ], 500);
            }

            $result = $response->json();

            // 5. حفظ الوجبات المقترحة في قاعدة البيانات
            // ملاحظة: قمت بتعديل أسماء الحقول لتطابق الرد القادم من main.py الجديد
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
                            'description'   => "وجبة مقترحة آلياً بناءً على حالتك الصحية",
                            'confidence'    => 0.95,
                            'created_at'    => now()
                        ]);
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'تم توليد وحفظ التوصيات الغذائية بنجاح',
                'data' => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'حدث خطأ أثناء التواصل مع خدمة البايثون',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}