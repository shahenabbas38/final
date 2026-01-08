<?php

namespace App\Http\Controllers;

use App\Models\NutritionRecommendation;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class NutritionRecommendationController extends Controller
{
    /**
     * ✅ الدالة الجديدة: عرض سجل التوصيات لجميع المرضى في لوحة التحكم (Admin Panel)
     * تم إضافتها لتخدم واجهة الـ Blade دون التأثير على الـ API
     */
    public function adminIndex()
    {
        // جلب التوصيات مع بيانات المريض المرتبطة وترتيبها من الأحدث
        $recommendations = NutritionRecommendation::with('patient')->latest()->paginate(15);
        
        return view('admin.nutrition.index', compact('recommendations'));
    }

    /**
     * عرض تاريخ التوصيات الخاص بالمريض الحالي فقط
     * GET /api/nutrition/recommendations
     */
    public function index()
    {
        // جلب المستخدم صاحب التوكن الحالي
        $user = Auth::user();
        
        // جلب السجلات المرتبطة بـ id هذا المريض فقط لضمان الخصوصية
        $recommendations = NutritionRecommendation::where('patient_id', $user->id)
            ->orderBy('created_at', 'desc') // ترتيب من الأحدث إلى الأقدم
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $recommendations
        ]);
    }

    /**
     * جلب آخر خطة غذائية تم توليدها للمريض مقسمة حسب الوجبات
     * GET /api/nutrition/my-plan
     */
    public function getMyRecommendations()
    {
        $user = Auth::user();

        // جلب آخر التوصيات لهذا المريض فقط وتقسيمها حسب نوع الوجبة
        $plan = NutritionRecommendation::where('patient_id', $user->id)
            ->latest()
            ->get()
            ->groupBy('meal_type'); 

        if ($plan->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'لا توجد خطة غذائية مسجلة لهذا الحساب'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'current_plan' => $plan
        ]);
    }

    /**
     * توليد توصيات جديدة عبر الاتصال بمحرك الذكاء الاصطناعي
     * POST /api/nutrition/recommendations/generate
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $profile = PatientProfile::where('user_id', $user->id)->first();
        
        if (!$profile) {
            return response()->json(['message' => 'بروفايل المريض غير موجود في النظام'], 404);
        }

        $pythonApiUrl = "https://python-production-9689.up.railway.app/recommend";

        try {
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

            // حفظ النتائج مرتبطة بـ ID المريض الحالي
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