<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MedicationSchedule;
use App\Models\MedicationIntake;
use Illuminate\Support\Facades\Auth;

class MedicationScheduleController extends Controller
{
    // 📥 عرض جداول المريض المسجل حالياً فقط
    public function index()
    {
        // جلب المعرف من التوكن المسجل
        $userId = Auth::id(); 

        $schedules = MedicationSchedule::where('patient_id', $userId)
            ->with(['medication', 'intakes'])
            ->get();

        return response()->json($schedules);
    }

    // 🟢 إنشاء جدول جديد مرتبط تلقائياً بصاحب التوكن
    public function store(Request $request)
    {
        $request->validate([
            'medication_id' => 'required|integer|exists:medications,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'times_per_day' => 'required|integer|min:1',
            'times_of_day' => 'required|array'
        ]);

        $schedule = MedicationSchedule::create([
            'patient_id' => Auth::id(), // الربط التلقائي بالمستخدم الحالي
            'medication_id' => $request->medication_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'times_per_day' => $request->times_per_day,
            'times_of_day' => json_encode($request->times_of_day),
        ]);

        // إنشاء جرعات intake تلقائياً
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

    // 📄 عرض جدول محدد (بشرط أن يخص المريض الحالي)
    public function show($id)
    {
        $schedule = MedicationSchedule::where('patient_id', Auth::id())
            ->with(['medication', 'intakes'])
            ->findOrFail($id);

        return response()->json($schedule);
    }

    // ✏️ تعديل الجدول (للمريض الحالي فقط)
    public function update(Request $request, $id)
    {
        $schedule = MedicationSchedule::where('patient_id', Auth::id())->findOrFail($id);
        
        $schedule->update($request->only([
            'medication_id', 'start_date', 'end_date', 'times_per_day'
        ]));

        return response()->json([
            'message' => 'Medication schedule updated successfully ✏️',
            'data' => $schedule
        ]);
    }

    // 🗑️ حذف الجدول (للمريض الحالي فقط)
    public function destroy($id)
    {
        $schedule = MedicationSchedule::where('patient_id', Auth::id())->findOrFail($id);
        
        $schedule->intakes()->delete();
        $schedule->delete();

        return response()->json([
            'message' => 'Medication schedule deleted successfully 🗑️'
        ]);
    }

    // ✅ تحديث حالة جرعة معينة
    public function updateIntake(Request $request, $intake_id)
    {
        // التحقق من أن الجرعة تتبع لجدول يملكه المريض الحالي
        $intake = MedicationIntake::whereHas('schedule', function($query) {
            $query->where('patient_id', Auth::id());
        })->findOrFail($intake_id);

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