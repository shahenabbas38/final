<?php

namespace App\Http\Controllers;

use App\Models\DoctorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // تم إضافة هذا لاستخدامه في الاستعلام

class DoctorProfileController extends Controller
{
    /**
     * 👁️ عرض بروفايل الطبيب الحالي (باستخدام التوكن)
     */
    public function showMyProfile()
    {
        $user = Auth::user();
        $profile = DoctorProfile::where('user_id', $user->id)
            ->with(['clinic', 'specialty'])
            ->first();

        if (!$profile) {
            return response()->json(['message' => 'Profile not found ❌'], 404);
        }

        return response()->json([
            'message' => 'Doctor profile fetched successfully ✅',
            'profile' => $profile,
        ]);
    }

    /**
     * ✏️ تحديث أو إنشاء بروفايل الطبيب الحالي (مع ساعات الدوام)
     */
    public function updateMyProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'full_name' => 'required|string|max:200',
            'gender' => 'in:male,female,other',
            'primary_specialty_id' => 'nullable|integer|exists:specialties,id',
            'clinic_id' => 'nullable|integer|exists:clinics,id',
            'license_no' => 'nullable|string|max:64|unique:doctor_profiles,license_no,' . $user->id . ',user_id',
            'bio' => 'nullable|string',
            'avatar_url' => 'nullable|url',
            
            // ✅ التحقق من الحقول الجديدة (ساعات الدوام)
            'working_days' => 'nullable|string|max:100', // مثال: "Sat,Sun,Mon"
            'start_time'   => 'nullable|date_format:H:i', // تنسيق الوقت 14:30
            'end_time'     => 'nullable|date_format:H:i|after:start_time',
            'shift_type'   => 'in:AM,PM,BOTH',
        ]);

        $profile = DoctorProfile::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return response()->json([
            'message' => 'Doctor profile updated successfully ✅',
            'profile' => $profile,
        ]);
    }

    /**
     * 🧾 عرض جميع البروفايلات (للإدارة أو للمرضى للبحث)
     */
    public function index()
    {
        $profiles = DoctorProfile::with(['user', 'clinic', 'specialty'])->get();

        return response()->json([
            'message' => 'All doctor profiles fetched successfully ✅',
            'profiles' => $profiles,
        ]);
    }

    /**
     * 🗑️ حذف بروفايل
     */
    public function destroy($id)
    {
        $profile = DoctorProfile::where('user_id', $id)->first();

        if (!$profile) {
            return response()->json(['message' => 'Profile not found ❌'], 404);
        }

        $profile->delete();

        return response()->json([
            'message' => 'Doctor profile deleted successfully 🗑️',
        ]);
    }

    /**
     * 👥 جلب قائمة المرضى الخاصين بهذا الدكتور فقط (الذين لديهم مواعيد معه)
     * ملاحظة: تمت إضافة هذه الدالة بناءً على طلبك
     */
    public function getMyPatients(Request $request)
    {
        $doctorId = Auth::id(); // الحصول على ID الدكتور من التوكن

        // جلب بيانات المرضى الفريدين من جدول المواعيد
        $patients = DB::table('appointments')
            ->join('patient_profiles', 'appointments.patient_id', '=', 'patient_profiles.user_id')
            ->where('appointments.doctor_id', $doctorId)
            ->select(
                'patient_profiles.user_id',
                'patient_profiles.full_name',
                'patient_profiles.gender',
                'patient_profiles.avatar_url',
                'patient_profiles.primary_condition'
            )
            ->distinct() // لعدم تكرار المريض إذا كان لديه أكثر من موعد
            ->get();

        return response()->json([
            'message' => 'My patients list fetched successfully ✅',
            'count' => $patients->count(),
            'patients' => $patients
        ]);
    }
}