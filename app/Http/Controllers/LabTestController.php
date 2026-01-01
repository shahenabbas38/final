<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LabTest;
use App\Models\LabResult;
use Illuminate\Support\Facades\Auth;

class LabTestController extends Controller
{
    /**
     * 🧪 جلب تحاليل المريض المسجل حالياً (بناءً على التوكن)
     * GET /api/my-lab-tests
     */
    public function getMyLabTests()
    {
        $user = Auth::user();

        // 1. التحقق من اكتمال الملف الشخصي
        if (!$user->patientProfile) {
            return response()->json([
                'message' => 'يرجى إتمام معلومات الملف الشخصي للوصول إلى التحاليل.'
            ], 403);
        }

        // 2. جلب التحاليل الخاصة بالمريض مع النتائج والأطباء الذين طلبوها
        $tests = LabTest::with(['doctor', 'results'])
            ->where('patient_id', $user->id)
            ->orderBy('ordered_at', 'desc')
            ->get();

        // 3. التحقق من وجود بيانات
        if ($tests->isEmpty()) {
            return response()->json([
                'message' => 'ليس لديك تحاليل طبية حالياً.',
                'data' => []
            ], 200);
        }

        return response()->json([
            'message' => 'تم جلب التحاليل بنجاح ✅',
            'data' => $tests
        ], 200);
    }

    /**
     * 📥 عرض جميع التحاليل (للمسؤولين أو الأطباء)
     */
    public function index()
    {
        $tests = LabTest::with(['patient', 'doctor', 'results'])
            ->orderBy('ordered_at', 'desc')
            ->paginate(15);

        return response()->json($tests);
    }

    /**
     * 🟢 إنشاء طلب تحليل جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|integer|exists:patient_profiles,user_id',
            'test_type'  => 'required|string|max:64',
            'lab_name'   => 'nullable|string|max:120',
            'due_at'     => 'nullable|date'
        ]);

        $test = LabTest::create([
            'patient_id'           => $request->patient_id,
            'ordered_by_doctor_id' => Auth::id(), // الطبيب المتصل هو من يطلب
            'test_type'            => $request->test_type,
            'lab_name'             => $request->lab_name,
            'status'               => 'ORDERED',
            'ordered_at'           => now(),
            'due_at'               => $request->due_at
        ]);

        return response()->json([
            'message' => 'Lab test created successfully ✅',
            'data'    => $test
        ], 201);
    }

    /**
     * 📄 عرض تحليل محدد بالتفصيل
     */
    public function show($id)
    {
        $test = LabTest::with(['patient', 'doctor', 'results'])->find($id);

        if (!$test) {
            return response()->json(['message' => 'التحليل غير موجود ❌'], 404);
        }

        return response()->json($test);
    }

    /**
     * 🧪 إضافة نتيجة للتحليل وتغيير حالته
     */
    public function addResult(Request $request, $id)
    {
        $test = LabTest::find($id);
        if (!$test) {
            return response()->json(['message' => 'التحليل غير موجود ❌'], 404);
        }

        $request->validate([
            'result_date'    => 'required|date',
            'value_numeric'  => 'nullable|numeric',
            'unit'           => 'nullable|string|max:32',
            'ref_range'      => 'nullable|string|max:64',
            'attachment_url' => 'nullable|string|max:255'
        ]);

        $result = LabResult::create([
            'lab_test_id'    => $test->id,
            'result_date'    => $request->result_date,
            'value_numeric'  => $request->value_numeric,
            'unit'           => $request->unit,
            'ref_range'      => $request->ref_range,
            'attachment_url' => $request->attachment_url
        ]);

        // تحديث حالة التحليل تلقائياً إلى "مكتمل"
        $test->update(['status' => 'COMPLETED']);

        return response()->json([
            'message' => 'Lab result added successfully 🧪',
            'data'    => $result
        ], 201);
    }

    /**
     * 🗑️ حذف التحليل
     */
    public function destroy($id)
    {
        $test = LabTest::find($id);
        if (!$test) {
            return response()->json(['message' => 'التحليل غير موجود ❌'], 404);
        }

        $test->results()->delete(); // حذف النتائج أولاً لضمان التكامل
        $test->delete();

        return response()->json(['message' => 'Lab test deleted successfully 🗑️']);
    }
}