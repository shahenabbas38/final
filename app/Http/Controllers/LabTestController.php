<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LabTest;
use App\Models\LabResult;
use Illuminate\Support\Facades\Auth;

class LabTestController extends Controller
{
    /**
     * 📥 عرض جميع التحاليل مع المريض والطبيب والنتائج
     */
    public function index()
    {
        $tests = LabTest::with(['patient', 'doctor', 'results'])
            ->orderBy('ordered_at', 'desc')
            ->paginate(10); // 🔸 يمكنك تغيير العدد أو إزالته إذا تريد get() فقط

        return response()->json($tests);
    }

    /**
     * 🟢 إنشاء طلب تحليل جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|integer|exists:patient_profiles,user_id',
            'test_type' => 'required|string|max:64',
            'lab_name' => 'nullable|string|max:120',
            'due_at' => 'nullable|date'
        ]);

        $test = LabTest::create([
            'patient_id' => $request->patient_id,
            'ordered_by_doctor_id' => Auth::id(),
            'test_type' => $request->test_type,
            'lab_name' => $request->lab_name,
            'status' => 'ORDERED',
            'ordered_at' => now(),
            'due_at' => $request->due_at
        ]);

        return response()->json([
            'message' => 'Lab test created successfully ✅',
            'data' => $test
        ], 201);
    }

    /**
     * 📄 عرض تحليل محدد حسب ID
     */
    public function show($id)
    {
        $test = LabTest::with(['patient', 'doctor', 'results'])->findOrFail($id);
        return response()->json($test);
    }

    /**
     * ✏️ تعديل حالة أو معلومات التحليل
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'nullable|string',
            'test_type' => 'nullable|string|max:64',
            'lab_name' => 'nullable|string|max:120',
            'due_at' => 'nullable|date'
        ]);

        $test = LabTest::findOrFail($id);

        // ✨ تحديد الحقول التي يمكن تعديلها فقط
        $test->update($request->only([
            'status',
            'test_type',
            'lab_name',
            'due_at'
        ]));

        return response()->json([
            'message' => 'Lab test updated successfully ✏️',
            'data' => $test
        ]);
    }

    /**
     * 🗑️ حذف التحليل والنتائج المرتبطة
     */
    public function destroy($id)
    {
        $test = LabTest::findOrFail($id);
        $test->results()->delete();
        $test->delete();

        return response()->json([
            'message' => 'Lab test deleted successfully 🗑️'
        ]);
    }

    /**
     * 🧪 إضافة نتيجة للتحليل
     */
    public function addResult(Request $request, $id)
    {
        $request->validate([
            'result_date' => 'required|date',
            'value_numeric' => 'nullable|numeric',
            'unit' => 'nullable|string|max:32',
            'ref_range' => 'nullable|string|max:64',
            'attachment_url' => 'nullable|string|max:255'
        ]);

        $test = LabTest::findOrFail($id);

        $result = LabResult::create([
            'lab_test_id' => $test->id,
            'result_date' => $request->result_date,
            'value_numeric' => $request->value_numeric,
            'unit' => $request->unit,
            'ref_range' => $request->ref_range,
            'attachment_url' => $request->attachment_url
        ]);

        // 🟡 تحديث حالة التحليل إلى "COMPLETED"
        $test->update(['status' => 'COMPLETED']);

        return response()->json([
            'message' => 'Lab result added successfully 🧪',
            'data' => $result
        ], 201);
    }
}
