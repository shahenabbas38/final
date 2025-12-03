<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PatientProfile;

class PatientProfileController extends Controller
{
    /**
     * 🧾 عرض بروفايل المريض الحالي (محمي بالتوكن)
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $profile = PatientProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            return response()->json(['message' => 'Profile not found ❌'], 404);
        }

        return response()->json([
            'message' => 'Profile retrieved successfully ✅',
            'profile' => $profile
        ]);
    }

    /**
     * 🩺 إنشاء أو تحديث بروفايل المريض
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'full_name'          => 'required|string|max:200',
            'gender'             => 'nullable|in:male,female,other',
            'dob'                => 'nullable|date',
            'height_cm'          => 'nullable|integer',
            'weight_kg'          => 'nullable|numeric',
            'primary_condition'  => 'nullable|string|max:64',
            'address'            => 'nullable|string|max:255',
            'emergency_contact'  => 'nullable|string|max:128',
            'avatar_url'         => 'nullable|string|max:255',
        ]);

        // إذا كان البروفايل موجود يقوم بتحديثه
        // وإذا غير موجود يقوم بإنشائه تلقائياً
        $profile = PatientProfile::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return response()->json([
            'message' => 'Patient profile saved successfully ✅',
            'profile' => $profile
        ]);
    }

    /**
     * 🧑‍⚕️ عرض جميع بروفايلات المرضى (لأغراض إدارية)
     */
    public function index()
    {
        $profiles = PatientProfile::with('user')->get();

        return response()->json([
            'message' => 'All patient profiles fetched successfully ✅',
            'profiles' => $profiles
        ]);
    }
}
