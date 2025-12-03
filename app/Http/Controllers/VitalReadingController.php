<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;   // ✅ تم إضافته
use App\Models\VitalReading;

class VitalReadingController extends Controller
{
    /**
     * 📥 عرض جميع القراءات
     * الطبيب: يرى جميع القراءات
     * المريض: يرى قراءاته فقط
     */
    public function index()
    {
        $user = Auth::user();  // ✅ تم التعديل

        if ($user->role === 'DOCTOR') {
            $readings = VitalReading::with('patient')
                ->orderBy('measured_at', 'desc')
                ->get();
        } elseif ($user->role === 'PATIENT') {
            $readings = VitalReading::with('patient')
                ->where('patient_id', $user->id)
                ->orderBy('measured_at', 'desc')
                ->get();
        } else {
            return response()->json(['message' => 'Access denied 🚫'], 403);
        }

        return response()->json($readings);
    }

    /**
     * 🟢 إنشاء قراءة جديدة
     * الطبيب فقط
     */
    public function store(Request $request)
    {
        $user = Auth::user();  // ✅ تم التعديل
        if ($user->role !== 'DOCTOR') {
            return response()->json(['message' => 'Access denied 🚫 — Only doctors can add readings'], 403);
        }

        $request->validate([
            'patient_id' => 'required|integer|exists:patient_profiles,user_id',
            'type' => 'required|string|max:24',
            'value' => 'required|numeric',
            'aux_value' => 'nullable|numeric',
            'measured_at' => 'required|date',
            'source' => 'nullable|in:MANUAL,DEVICE',
            'note' => 'nullable|string|max:255'
        ]);

        $reading = VitalReading::create($request->all());

        return response()->json([
            'message' => 'Vital reading created successfully ✅',
            'data' => $reading
        ], 201);
    }

    /**
     * 📄 عرض قراءة محددة
     * الطبيب: يرى كل شيء
     * المريض: يرى فقط قراءاته
     */
    public function show($id)
    {
        $user = Auth::user();  // ✅ تم التعديل
        $reading = VitalReading::findOrFail($id);

        if ($user->role === 'PATIENT' && $reading->patient_id !== $user->id) {
            return response()->json(['message' => 'Access denied 🚫'], 403);
        }

        return response()->json($reading);
    }

    /**
     * ✏️ تعديل القراءة
     * الطبيب فقط
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();  // ✅ تم التعديل
        if ($user->role !== 'DOCTOR') {
            return response()->json(['message' => 'Access denied 🚫 — Only doctors can update readings'], 403);
        }

        $reading = VitalReading::findOrFail($id);
        $reading->update($request->only(['type', 'value', 'aux_value', 'measured_at', 'source', 'note']));

        return response()->json([
            'message' => 'Vital reading updated successfully ✏️',
            'data' => $reading
        ]);
    }

    /**
     * 🗑️ حذف القراءة
     * الطبيب فقط
     */
    public function destroy($id)
    {
        $user = Auth::user();  // ✅ تم التعديل
        if ($user->role !== 'DOCTOR') {
            return response()->json(['message' => 'Access denied 🚫 — Only doctors can delete readings'], 403);
        }

        $reading = VitalReading::findOrFail($id);
        $reading->delete();

        return response()->json(['message' => 'Vital reading deleted successfully 🗑️']);
    }
}
