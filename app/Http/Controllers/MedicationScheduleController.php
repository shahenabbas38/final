<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MedicationSchedule;
use App\Models\MedicationIntake;

class MedicationScheduleController extends Controller
{
    // 📥 عرض جميع الجداول
    public function index()
    {
        $schedules = MedicationSchedule::with(['medication', 'intakes', 'patient'])->get();
        return response()->json($schedules);
    }

    // 🟢 إنشاء جدول جديد للدواء
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|integer',
            'medication_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'times_per_day' => 'required|integer|min:1',
            'times_of_day' => 'required|array'
        ]);

        $schedule = MedicationSchedule::create([
            'patient_id' => $request->patient_id,
            'medication_id' => $request->medication_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'times_per_day' => $request->times_per_day,
            'times_of_day' => json_encode($request->times_of_day),
        ]);

        // إنشاء جرعات intake تلقائياً (اختياري)
        foreach ($request->times_of_day as $time) {
            MedicationIntake::create([
                'schedule_id' => $schedule->id,
                'planned_time' => date("Y-m-d H:i:s", strtotime($schedule->start_date . ' ' . $time)),
                'status' => 'PLANNED'
            ]);
        }

        return response()->json([
            'message' => 'Medication schedule created successfully ✅',
            'data' => $schedule->load(['medication', 'intakes'])
        ], 201);
    }

    // 📄 عرض جدول محدد
    public function show($id)
    {
        $schedule = MedicationSchedule::with(['medication', 'intakes', 'patient'])->findOrFail($id);
        return response()->json($schedule);
    }

    // ✏️ تعديل
    public function update(Request $request, $id)
    {
        $schedule = MedicationSchedule::findOrFail($id);
        $schedule->update($request->all());

        return response()->json([
            'message' => 'Medication schedule updated successfully ✏️',
            'data' => $schedule
        ]);
    }

    // 🗑️ حذف جدول مع الجرعات المرتبطة
    public function destroy($id)
    {
        $schedule = MedicationSchedule::findOrFail($id);
        $schedule->intakes()->delete();
        $schedule->delete();

        return response()->json([
            'message' => 'Medication schedule deleted successfully 🗑️'
        ]);
    }

    // ✅ تحديث حالة جرعة
    public function updateIntake(Request $request, $intake_id)
    {
        $intake = MedicationIntake::findOrFail($intake_id);
        $intake->update([
            'status' => $request->status,
            'taken_time' => $request->taken_time ?? now()
        ]);

        return response()->json([
            'message' => 'Intake updated successfully ✅',
            'data' => $intake
        ]);
    }
}
