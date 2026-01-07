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
        // 1. جلب المريض من التوكن
        $user = Auth::user();
        
        // 2. جلب بيانات المريض من قاعدة البيانات
        $profile = PatientProfile::where('user_id', $user->id)->first();
        
        if (!$profile) {
            return response()->json(['message' => 'بروفايل المريض غير موجود في النظام'], 404);
        }

        // 3. الرابط الخاص بك الذي يعمل الآن على Railway
        // تم وضع رابطك هنا: python-production-9689.up.railway.app
        $pythonApiUrl = "https://python-production-9689.up.railway.app/recommend";

        try {
            // 4. إرسال البيانات لخدمة البايثون
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
                    'error' => 'تعذر الاتصال بمحرك الذكاء الاصطناعي',
                    'details' => $response->body()
                ], 500);
            }

            $result = $response->json();

            // 5. حفظ النتائج في قاعدة بيانات MySQL
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
                            'description'   => "توصية ذكية بناءً على الحالة الصحية",
                            'confidence'    => 0.95,
                            'created_at'    => now()
                        ]);
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'تم توليد الخطة الغذائية بنجاح',
                'data' => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'حدث خطأ فني',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}