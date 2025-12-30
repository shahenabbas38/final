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

        // 3. تحديد المسار المطلق لملف البايثون
        $pythonPath = base_path('ai/food_recommendation.py');
        
        if (!file_exists($pythonPath)) {
            return response()->json([
                'error' => 'الملف البرمجي للبايثون غير موجود',
                'checked_path' => $pythonPath
            ], 500);
        }

        // 4. تنفيذ الأمر مع إجبار النظام على البحث في مسارات Nixpacks
        $jsonParams = json_encode($patientParams);
        
        // تعديل جوهري: إضافة المسارات الشائعة لبيئات Nixpacks و Heroku في Railway
        $envPaths = "PATH=\$PATH:/usr/bin:/usr/local/bin:/opt/nix/bin:/nix/var/nix/profiles/default/bin";
        $command = "$envPaths python3 " . escapeshellarg($pythonPath) . " " . escapeshellarg($jsonParams) . " 2>&1";
        
        $output = shell_exec($command);
        $result = json_decode($output, true);

        // 5. فحص النتيجة ومعالجة الخطأ
        if (is_null($result)) {
            return response()->json([
                'error' => 'فشل تشغيل محرك الذكاء الاصطناعي',
                'debug_info' => $output, // هنا سيظهر السبب الحقيقي (نقص مكتبة أو مسار)
                'suggestion' => 'تحقق من الـ Build Logs في Railway للتأكد من تثبيت pandas',
                'path_used' => $pythonPath
            ], 500);
        }

        if (isset($result['error'])) {
            return response()->json([
                'error' => 'خطأ داخلي في سكربت البايثون',
                'details' => $result['error']
            ], 500);
        }

        // 6. حفظ الوجبات في قاعدة البيانات
        foreach (['breakfast', 'lunch', 'dinner'] as $mealCategory) {
            if (isset($result[$mealCategory]) && is_array($result[$mealCategory])) {
                foreach ($result[$mealCategory] as $meal) {
                    NutritionRecommendation::create([
                        'patient_id'    => $user->id,
                        'food_name'     => $meal['food_name'],
                        'meal_type'     => $meal['meal_type'],
                        'calories'      => $meal['calories'] ?? 0,
                        'protein'       => $meal['protein'] ?? 0,
                        'carbohydrates' => $meal['carbohydrates'] ?? 0,
                        'fat'           => $meal['fat'] ?? 0,
                        'description'   => $meal['description'] ?? '',
                        'confidence'    => $meal['confidence'] ?? 0,
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