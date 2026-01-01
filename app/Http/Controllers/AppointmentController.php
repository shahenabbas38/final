<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    /**
     * 👥 جلب عدد المرضى الفريدين التابعين للدكتور (التعديل الجديد)
     * يعتمد على عدد المرضى الذين لديهم سجل مواعيد مع الطبيب الحالي
     */
    public function getDoctorPatientsCount()
    {
        $doctorId = Auth::id(); // استخراج معرف الطبيب من التوكن

        // حساب عدد المرضى بدون تكرار (بناءً على حقل patient_id في جدول المواعيد)
        $count = Appointment::where('doctor_id', $doctorId)
            ->distinct('patient_id')
            ->count('patient_id');

        return response()->json([
            'message' => 'تم جلب عدد المرضى بنجاح ✅',
            'patients_count' => $count
        ], 200);
    }

    /**
     * 📅 جلب مواعيد المريض بناءً على التوكن
     */
    public function getPatientAppointments()
    {
        $user = Auth::user();

        $profile = PatientProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            return response()->json([
                'message' => 'يرجى إتمام معلومات الملف الشخصي أولاً للوصول إلى المواعيد.'
            ], 403);
        }

        $appointments = Appointment::with(['doctor', 'clinic'])
            ->where('patient_id', $user->id)
            ->orderBy('start_at', 'asc')
            ->get();

        if ($appointments->isEmpty()) {
            return response()->json([
                'message' => 'ليس لديك مواعيد حالياً.',
                'appointments' => []
            ], 200);
        }

        return response()->json([
            'message' => 'تم جلب مواعيدك بنجاح ✅',
            'appointments' => $appointments,
        ]);
    }

    /**
     * 📅 إنشاء موعد جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id'  => 'required|exists:doctor_profiles,user_id',
            'patient_id' => 'required|exists:patient_profiles,user_id',
            'clinic_id'  => 'nullable|exists:clinics,id',
            'start_at'   => 'required|date|after:now',
            'end_at'     => 'nullable|date|after:start_at',
            'reason'     => 'nullable|string|max:255',
        ]);

        $appointment = Appointment::create([
            'doctor_id'         => $request->doctor_id,
            'patient_id'        => $request->patient_id,
            'clinic_id'         => $request->clinic_id,
            'start_at'          => $request->start_at,
            'end_at'            => $request->end_at,
            'status'            => 'PENDING',
            'reason'            => $request->reason,
            'created_by_user_id'=> Auth::id(),
        ]);

        return response()->json([
            'message' => 'Appointment created successfully ✅',
            'appointment' => $appointment->load(['doctor', 'patient', 'clinic']),
        ], 201);
    }

    /**
     * 📋 عرض جميع المواعيد
     */
    public function index()
    {
        $appointments = Appointment::with(['doctor', 'patient', 'clinic'])
            ->orderBy('start_at', 'asc')
            ->get();

        return response()->json([
            'message' => 'All appointments fetched successfully ✅',
            'appointments' => $appointments,
        ]);
    }

    /**
     * 🔍 عرض موعد محدد
     */
    public function show($id)
    {
        $appointment = Appointment::with(['doctor', 'patient', 'clinic'])->find($id);
        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found ❌'], 404);
        }

        return response()->json(['appointment' => $appointment]);
    }

    /**
     * ✏️ تعديل موعد
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found ❌'], 404);
        }

        $request->validate([
            'start_at' => 'nullable|date',
            'end_at'   => 'nullable|date|after:start_at',
            'status'   => 'in:PENDING,CONFIRMED,CANCELLED,COMPLETED',
            'reason'   => 'nullable|string|max:255',
        ]);

        $appointment->update($request->only([
            'start_at', 'end_at', 'status', 'reason'
        ]));

        return response()->json([
            'message' => 'Appointment updated successfully ✅',
            'appointment' => $appointment->load(['doctor', 'patient', 'clinic']),
        ]);
    }

    /**
     * 🗑️ حذف موعد
     */
    public function destroy($id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found ❌'], 404);
        }

        $appointment->delete();
        return response()->json(['message' => 'Appointment deleted successfully ✅']);
    }
}