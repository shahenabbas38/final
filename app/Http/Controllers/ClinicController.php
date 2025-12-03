<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    // 📋 عرض كل العيادات
    public function index()
    {
        return response()->json([
            'message' => 'All clinics fetched successfully ✅',
            'clinics' => Clinic::all(),
        ]);
    }

    // ➕ إضافة عيادة جديدة
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'timezone' => 'nullable|string|max:64',
            'phone' => 'nullable|string|max:32',
        ]);

        $clinic = Clinic::create($validated);

        return response()->json([
            'message' => 'Clinic created successfully ✅',
            'clinic' => $clinic,
        ], 201);
    }

    // 👁️ عرض عيادة واحدة
    public function show($id)
    {
        $clinic = Clinic::find($id);
        if (!$clinic) return response()->json(['message' => 'Clinic not found ❌'], 404);

        return response()->json(['clinic' => $clinic]);
    }

    // ✏️ تعديل عيادة
    public function update(Request $request, $id)
    {
        $clinic = Clinic::find($id);
        if (!$clinic) return response()->json(['message' => 'Clinic not found ❌'], 404);

        $clinic->update($request->all());
        return response()->json(['message' => 'Clinic updated successfully ✅', 'clinic' => $clinic]);
    }

    // 🗑️ حذف عيادة
    public function destroy($id)
    {
        $clinic = Clinic::find($id);
        if (!$clinic) return response()->json(['message' => 'Clinic not found ❌'], 404);

        $clinic->delete();
        return response()->json(['message' => 'Clinic deleted successfully ✅']);
    }
}
