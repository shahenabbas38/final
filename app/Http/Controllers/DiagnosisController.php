<?php

namespace App\Http\Controllers;

use App\Models\Diagnosis;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiagnosisController extends Controller
{
    /**
     * 📥 عرض كل التشخيصات للمستخدم الحالي حسب دوره (مريض - طبيب - أدمن)
     */
    public function index()
    {
        $user = Auth::user();

        $with = [
            'medicalRecord:id,doctor_id,patient_id,visit_date',
            'doctor:user_id,full_name',
            'patient:user_id,full_name'
        ];

        if ($user->role === 'PATIENT') {
            $diagnoses = Diagnosis::with($with)
                ->whereHas('medicalRecord', function ($q) use ($user) {
                    $q->where('patient_id', $user->id);
                })
                ->get();
        } elseif ($user->role === 'DOCTOR') {
            $diagnoses = Diagnosis::with($with)
                ->whereHas('medicalRecord', function ($q) use ($user) {
                    $q->where('doctor_id', $user->id);
                })
                ->get();
        } else { // ADMIN
            $diagnoses = Diagnosis::with($with)->get();
        }

        return response()->json([
            'message' => 'Diagnoses fetched successfully ✅',
            'count' => $diagnoses->count(),
            'data' => $diagnoses,
        ]);
    }

    /**
     * ➕ إنشاء تشخيص جديد (طبيب فقط)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'DOCTOR') {
            return response()->json(['message' => 'Only doctors can add diagnoses ❌'], 403);
        }

        $validated = $request->validate([
            'medical_record_id' => 'required|exists:medical_records,id',
            'code' => 'nullable|string|max:32',
            'label' => 'required|string|max:200',
            'severity' => 'nullable|string|max:16',
        ]);

        // تأكد أن الطبيب هو صاحب السجل الطبي
        $record = MedicalRecord::where('id', $validated['medical_record_id'])
            ->where('doctor_id', $user->id)
            ->firstOrFail();

        $diagnosis = Diagnosis::create($validated);

        return response()->json([
            'message' => 'Diagnosis added successfully ✅',
            'data' => $diagnosis->load([
                'medicalRecord:id,doctor_id,patient_id,visit_date',
                'doctor:user_id,full_name',
                'patient:user_id,full_name'
            ]),
        ], 201);
    }

    /**
     * 🗑️ حذف تشخيص (للطبيب فقط)
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $diagnosis = Diagnosis::findOrFail($id);

        if ($user->role !== 'DOCTOR') {
            return response()->json(['message' => 'Only doctors can delete diagnoses ❌'], 403);
        }

        // تحقق أن الطبيب صاحب السجل
        if ($diagnosis->medicalRecord->doctor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized ❌'], 403);
        }

        $diagnosis->delete();

        return response()->json(['message' => 'Diagnosis deleted successfully 🗑️']);
    }
}
