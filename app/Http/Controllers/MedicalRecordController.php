<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalRecordController extends Controller
{
    /**
     * 📋 عرض جميع السجلات الطبية (مع إمكانية فلترة)
     */
    public function index(Request $request)
    {
        $query = MedicalRecord::with(['patient', 'doctor', 'diagnoses']);

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $records = $query->orderBy('visit_date', 'desc')->get();

        return response()->json(['records' => $records]);
    }

    /**
     * ➕ إنشاء سجل طبي جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patient_profiles,user_id',
            'doctor_id'  => 'nullable|exists:doctor_profiles,user_id',
            'visit_date' => 'required|date',
            'notes'      => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan'       => 'nullable|string',
        ]);

        $record = MedicalRecord::create($request->all());

        return response()->json([
            'message' => 'Medical record created successfully ✅',
            'record'  => $record
        ], 201);
    }

    /**
     * 📄 عرض سجل طبي محدد
     */
    public function show($id)
    {
        $record = MedicalRecord::with(['patient', 'doctor', 'diagnoses'])->findOrFail($id);
        return response()->json($record);
    }

    /**
     * ✏️ تعديل سجل طبي
     */
    public function update(Request $request, $id)
    {
        $record = MedicalRecord::findOrFail($id);

        $request->validate([
            'visit_date' => 'nullable|date',
            'notes'      => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan'       => 'nullable|string',
        ]);

        $record->update($request->all());

        return response()->json([
            'message' => 'Medical record updated successfully ✅',
            'record'  => $record
        ]);
    }

    /**
     * 🗑️ حذف سجل طبي
     */
    public function destroy($id)
    {
        $record = MedicalRecord::findOrFail($id);
        $record->delete();

        return response()->json(['message' => 'Medical record deleted successfully ✅']);
    }
}
