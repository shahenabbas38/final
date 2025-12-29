<?php

namespace App\Http\Controllers;

use App\Models\NutritionRecommendation;
use App\Models\PatientProfile; // تأكد من استدعاء موديل البروفايل
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NutritionRecommendationController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // 1. جلب بيانات المريض اللازمة للحسابات
        $profile = PatientProfile::where('user_id', $user->id)->first();
        
        if (!$profile) {
            return response()->json(['message' => 'بروفايل المريض غير مكتمل'], 404);
        }

        // 2. تحضير البيانات لإرسالها للذكاء الاصطناعي
        $patientData = json_encode([
            'weight_kg' => $profile->weight_kg,
            'height_cm' => $profile->height_cm,
            'gender' => $profile->gender,
            'primary_condition' => $profile->primary_condition
        ]);

        // 3. تشغيل السكريبت
        $pythonPath = base_path('food_recommendation.py');
        $command = "python3 " . escapeshellarg($pythonPath) . " " . escapeshellarg($patientData);
        $output = shell_exec($command);
        $result = json_decode($output, true);

        if (!$result || isset($result['error'])) {
            return response()->json(['error' => 'خطأ في نظام التوصيات', 'details' => $result['error'] ?? 'No output'], 500);
        }

        // 4. حفظ الـ 15 توصية (فطور، غداء، عشاء) في قاعدة البيانات
        $savedData = [];
        foreach (['breakfast', 'lunch', 'dinner'] as $mealKey) {
            foreach ($result[$mealKey] as $item) {
                $savedData[] = NutritionRecommendation::create([
                    'patient_id'    => $user->id,
                    'food_name'     => $item['food_name'],
                    'meal_type'     => $item['meal_type'],
                    'calories'      => $item['calories'],
                    'protein'       => $item['protein'],
                    'carbohydrates' => $item['carbohydrates'],
                    'fat'           => $item['fat'],
                    'description'   => $item['description'],
                    'confidence'    => $item['confidence'],
                ]);
            }
        }

        return response()->json([
            'message' => 'تم توليد خطة غذائية كاملة (15 وجبة)',
            'data' => $result // نرجع الـ JSON المرتب مباشرة للفلاتر
        ], 201);
    }

    // جلب توصيات المريض المسجل فقط مرتبة
    public function getMyRecommendations()
    {
        $recommendations = NutritionRecommendation::where('patient_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('meal_type'); // تقسيمهم حسب نوع الوجبة

        return response()->json($recommendations);
    }
}