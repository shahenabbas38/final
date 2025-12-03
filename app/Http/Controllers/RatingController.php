<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Rating;

class RatingController extends Controller
{
    /**
     * 📥 عرض جميع التقييمات
     * الطبيب يرى تقييماته فقط
     * المريض يرى تقييماته التي قام بها فقط
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'DOCTOR') {
            $ratings = Rating::with(['patient', 'appointment'])
                ->where('doctor_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($user->role === 'PATIENT') {
            $ratings = Rating::with(['doctor', 'appointment'])
                ->where('patient_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            return response()->json(['message' => 'Access denied 🚫'], 403);
        }

        return response()->json($ratings);
    }

    /**
     * 🟢 إضافة تقييم
     * المريض فقط من يستطيع التقييم
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'PATIENT') {
            return response()->json(['message' => 'Access denied 🚫 — Only patients can rate doctors'], 403);
        }

        $request->validate([
            'doctor_id' => 'required|integer|exists:users,id',
            'appointment_id' => 'nullable|integer|exists:appointments,id',
            'stars' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:255'
        ]);

        $rating = Rating::create([
            'doctor_id' => $request->doctor_id,
            'patient_id' => $user->id,
            'appointment_id' => $request->appointment_id,
            'stars' => $request->stars,
            'comment' => $request->comment,
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Rating added successfully ✅',
            'data' => $rating
        ], 201);
    }

    /**
     * 📄 عرض تقييم محدد
     */
    public function show($id)
    {
        $user = Auth::user();
        $rating = Rating::findOrFail($id);

        // الطبيب يشوف تقييمه — المريض يشوف تقييمه فقط
        if (
            ($user->role === 'DOCTOR' && $rating->doctor_id !== $user->id) ||
            ($user->role === 'PATIENT' && $rating->patient_id !== $user->id)
        ) {
            return response()->json(['message' => 'Access denied 🚫'], 403);
        }

        return response()->json($rating);
    }

    /**
     * 🗑️ حذف تقييم (اختياري)
     * المريض فقط يقدر يحذف تقييمه
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $rating = Rating::findOrFail($id);

        if ($user->role !== 'PATIENT' || $rating->patient_id !== $user->id) {
            return response()->json(['message' => 'Access denied 🚫'], 403);
        }

        $rating->delete();

        return response()->json(['message' => 'Rating deleted successfully 🗑️']);
    }
}
