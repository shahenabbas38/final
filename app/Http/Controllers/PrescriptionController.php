<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prescription;
use App\Models\PrescriptionItem;

class PrescriptionController extends Controller
{
    // 🟢 إنشاء وصفة جديدة مع عناصرها
    public function store(Request $request)
    {
        $request->validate([
            'medical_record_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.medication_id' => 'required|integer',
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

        // إنشاء العناصر (الأدوية)
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

        return response()->json(['message' => 'Prescription created successfully', 'data' => $prescription->load('items')]);
    }

    // 📥 عرض كل الوصفات
    public function index()
    {
        $prescriptions = Prescription::with(['items', 'medicalRecord'])->get();
        return response()->json($prescriptions);
    }

    // 📄 عرض وصفة محددة
    public function show($id)
    {
        $prescription = Prescription::with(['items', 'medicalRecord'])->findOrFail($id);
        return response()->json($prescription);
    }

    // 🗑️ حذف وصفة
    public function destroy($id)
    {
        $prescription = Prescription::findOrFail($id);
        $prescription->items()->delete();
        $prescription->delete();

        return response()->json(['message' => 'Prescription deleted successfully']);
    }
}
