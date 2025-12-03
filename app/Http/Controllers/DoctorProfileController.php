<?php

namespace App\Http\Controllers;

use App\Models\DoctorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * ✏️ تحديث أو إنشاء بروفايل الطبيب الحالي
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
     * 🧾 عرض جميع البروفايلات (للإدارة فقط)
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
     * 🗑️ حذف بروفايل (اختياري)
     */
    public function destroy($id)
    {
        $profile = DoctorProfile::where('user_id', $id)->first();

        if (!$profile) {
            return response()->json(['message' => 'Doctor profile not found ❌'], 404);
        }

        $profile->delete();

        return response()->json(['message' => 'Doctor profile deleted successfully ✅']);
    }
}
