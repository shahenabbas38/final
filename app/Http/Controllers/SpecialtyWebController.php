<?php

namespace App\Http\Controllers;

use App\Models\Specialty; 
use Illuminate\Http\Request;

class SpecialtyWebController extends Controller
{
    // 1. عرض البيانات في الجدول
    public function index()
    {
        $specialties = Specialty::all();
        return view('admin.specialties.index', compact('specialties'));
    }

    // 2. حفظ تخصص جديد
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:specialties,name|max:100'
        ]);

        Specialty::create($request->all());

        return redirect()->back()->with('success', 'تمت إضافة التخصص بنجاح ✅');
    }

    /**
     * 3. عرض صفحة التعديل (الدالة التي كانت مفقودة)
     */
    public function edit($id)
    {
        $specialty = Specialty::findOrFail($id);
        return view('admin.specialties.edit', compact('specialty'));
    }

    /**
     * 4. تحديث البيانات في قاعدة البيانات (الدالة التي كانت مفقودة)
     */
    public function update(Request $request, $id)
    {
        $specialty = Specialty::findOrFail($id);

        $request->validate([
            'name' => 'required|max:100|unique:specialties,name,' . $id
        ]);

        $specialty->update($request->all());

        return redirect()->route('admin.specialties.index')->with('success', 'تم تحديث التخصص بنجاح ✅');
    }

    // 5. حذف التخصص
    public function destroy($id)
    {
        $specialty = Specialty::findOrFail($id);
        $specialty->delete();

        return redirect()->back()->with('success', 'تم الحذف بنجاح 🗑️');
    }
}