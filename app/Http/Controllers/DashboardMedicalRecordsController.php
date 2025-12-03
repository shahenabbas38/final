<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MedicalRecord;

class DashboardMedicalRecordsController extends Controller
{
    /**
     * 📋 يرجع السجلات الطبية للمستخدم حسب الدور:
     * - لو مريض -> سجلاته فقط
     * - لو طبيب -> سجلات مرضاه التي كتبها
     * - لو أدمن -> الكل (اختياري)
     */
    public function myMedicalRecords(Request $request)
    {
        $user = Auth::user();

        // تحميل علاقات أساسية للعرض
        $with = [
            'patient:user_id,full_name',
            'doctor:user_id,full_name',
        ];

        if ($user->role === 'PATIENT') {
            $records = MedicalRecord::with($with)
                ->where('patient_id', $user->id)
                ->orderBy('visit_date', 'desc')
                ->get();

        } elseif ($user->role === 'DOCTOR') {
            $records = MedicalRecord::with($with)
                ->where('doctor_id', $user->id)
                ->orderBy('visit_date', 'desc')
                ->get();

        } else { // ADMIN (اختياري)
            $records = MedicalRecord::with($with)
                ->orderBy('visit_date', 'desc')
                ->get();
        }

        return response()->json([
            'message' => 'Records fetched successfully ✅',
            'count'   => $records->count(),
            'data'    => $records,
        ]);
    }

    /**
     * ➕ (اختياري) إنشاء سجل طبي — للطبيب فقط — ليساعدك بالاختبار
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'DOCTOR') {
            return response()->json(['message' => 'Only doctors can create records.'], 403);
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_profiles,user_id',
            'visit_date' => 'required|date',
            'notes'      => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan'       => 'nullable|string',
        ]);

        $record = MedicalRecord::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id'  => $user->id, // الطبيب الحالي
            'visit_date' => $validated['visit_date'],
            'notes'      => $validated['notes'] ?? null,
            'assessment' => $validated['assessment'] ?? null,
            'plan'       => $validated['plan'] ?? null,
        ]);

        return response()->json([
            'message' => 'Medical record created ✅',
            'data'    => $record->load(['patient:user_id,full_name', 'doctor:user_id,full_name']),
        ], 201);
    }
}
