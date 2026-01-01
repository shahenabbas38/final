<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\MedicalRecord;
use Illuminate\Support\Facades\Auth;

class PrescriptionController extends Controller
{
    /**
     * 💊 جلب وصفات المريض المسجل حالياً (بناءً على التوكن)
     * تظهر الوصفات الخاصة بالمريض فقط
     */
    public function getMyPrescriptions()
    {
        $user = Auth::user();

        // التحقق من وجود ملف شخصي للمريض
        if (!$user->patientProfile) {
            return response()->json([
                'message' => 'يرجى إتمام معلومات الملف الشخصي أولاً'
            ], 403);
        }

        // جلب الوصفات المرتبطة بالسجلات الطبية الخاصة بهذا المريض
        // العلاقة: Prescription -> MedicalRecord -> Patient (User)
        $prescriptions = Prescription::whereHas('medicalRecord', function($query) use ($user) {
            $query->where('patient_id', $user->id);
        })
        ->with(['items.medication', 'medicalRecord'])
        ->orderBy('start_date', 'desc')
        ->get();

        if ($prescriptions->isEmpty()) {
            return response()->json([
                'message' => 'ليس لديك وصفات طبية حالياً',
                'data' => []
            ], 200);
        }

        return response()->json([
            'message' => 'تم جلب الوصفات بنجاح ✅',
            'data' => $prescriptions
        ]);
    }

    /**
     * 🟢 إنشاء وصفة جديدة مع عناصرها
     */
    public function store(Request $request)
    {
        $request->validate([
            'medical_record_id' => 'required|integer|exists:medical_records,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.medication_id' => 'required|integer|exists:medications,id',
            'items.*.dose_amount' => 'required|string',
            'items.*.frequency' => 'required|string',
        ]);

        // إنشاء الوصفة
        $prescription = Prescription::create([
            'medical_record_id' => $request->medical_record_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'notes' => $request->notes,
        ]);

        // إنشاء العناصر (الأدوية) بشكل جماعي لتحسين الأداء
        foreach ($request->items as $item) {
            PrescriptionItem::create([
                'prescription_id' => $prescription->id,
                'medication_id' => $item['medication_id'],
                'dose_amount' => $item['dose_amount'],
                'frequency' => $item['frequency'],
                'route' => $item['route'] ?? null,
                'instructions' => $item['instructions'] ?? null,
            ]);
        }

        return response()->json([
            'message' => 'تم إنشاء الوصفة الطبية بنجاح ✅',
            'data' => $prescription->load('items.medication')
        ], 201);
    }

    /**
     * 📥 عرض كل الوصفات (للمسؤولين أو الأطباء)
     */
    public function index()
    {
        $prescriptions = Prescription::with(['items.medication', 'medicalRecord.patient'])->get();
        return response()->json($prescriptions);
    }

    /**
     * 📄 عرض وصفة محددة بالتفصيل
     */
    public function show($id)
    {
        $prescription = Prescription::with(['items.medication', 'medicalRecord'])->find($id);
        
        if (!$prescription) {
            return response()->json(['message' => 'الوصفة الطبية غير موجودة ❌'], 404);
        }

        return response()->json($prescription);
    }

    /**
     * 🗑️ حذف وصفة
     */
    public function destroy($id)
    {
        $prescription = Prescription::find($id);
        
        if (!$prescription) {
            return response()->json(['message' => 'الوصفة غير موجودة ❌'], 404);
        }

        // حذف العناصر المرتبطة أولاً (اختياري إذا كان الـ DB يحتوي على ON DELETE CASCADE)
        $prescription->items()->delete();
        $prescription->delete();

        return response()->json(['message' => 'تم حذف الوصفة بنجاح 🗑️']);
    }
}