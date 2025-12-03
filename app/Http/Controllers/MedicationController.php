<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medication;

class MedicationController extends Controller
{
    // 📥 عرض جميع الأدوية
    public function index()
    {
        $medications = Medication::all();
        return response()->json($medications);
    }

    // 🟢 إنشاء دواء جديد
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:160',
            'form' => 'nullable|string|max:64',
            'strength' => 'nullable|string|max:64',
            'unit' => 'nullable|string|max:32'
        ]);

        $medication = Medication::create($request->all());

        return response()->json([
            'message' => 'Medication created successfully ✅',
            'data' => $medication
        ], 201);
    }

    // 📄 عرض دواء محدد
    public function show($id)
    {
        $medication = Medication::findOrFail($id);
        return response()->json($medication);
    }

    // ✏️ تعديل دواء
    public function update(Request $request, $id)
    {
        $medication = Medication::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:160',
            'form' => 'nullable|string|max:64',
            'strength' => 'nullable|string|max:64',
            'unit' => 'nullable|string|max:32'
        ]);

        $medication->update($request->all());

        return response()->json([
            'message' => 'Medication updated successfully ✏️',
            'data' => $medication
        ]);
    }

    // 🗑️ حذف دواء
    public function destroy($id)
    {
        $medication = Medication::findOrFail($id);
        $medication->delete();

        return response()->json([
            'message' => 'Medication deleted successfully 🗑️'
        ]);
    }
}
