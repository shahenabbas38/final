<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use Illuminate\Http\Request;

class SpecialtyController extends Controller
{
    // 📋 عرض جميع الاختصاصات
    public function index()
    {
        return response()->json([
            'message' => 'All specialties fetched successfully ✅',
            'specialties' => Specialty::all(),
        ]);
    }

    // ➕ إضافة اختصاص جديد
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:specialties,name',
        ]);

        $specialty = Specialty::create($validated);

        return response()->json([
            'message' => 'Specialty created successfully ✅',
            'specialty' => $specialty,
        ], 201);
    }

    // 🗑️ حذف اختصاص
    public function destroy($id)
    {
        $specialty = Specialty::find($id);
        if (!$specialty) return response()->json(['message' => 'Specialty not found ❌'], 404);

        $specialty->delete();
        return response()->json(['message' => 'Specialty deleted successfully ✅']);
    }
}
