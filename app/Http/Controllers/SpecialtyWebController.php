<?php

namespace App\Http\Controllers;

// استيراد الموديل الخاص بالتخصصات
use App\Models\Specialty; 
use Illuminate\Http\Request;

class SpecialtyWebController extends Controller
{
    // تعليمات عرض البيانات في الجدول
    public function index()
    {
        $specialties = Specialty::all();
        return view('admin.specialties.index', compact('specialties'));
    }

    // تعليمات حفظ تخصص جديد في قاعدة البيانات
    public function store(Request $request)
    {
        // التحقق من البيانات
        $request->validate([
            'name' => 'required|unique:specialties,name|max:100'
        ]);

        // الحفظ
        Specialty::create($request->all());

        // إعادة التوجيه مع رسالة نجاح لـ Toastr
        return redirect()->back()->with('success', 'تمت إضافة التخصص بنجاح ✅');
    }

    // تعليمات حذف التخصص
    public function destroy($id)
    {
        $specialty = Specialty::findOrFail($id);
        $specialty->delete();

        return redirect()->back()->with('success', 'تم الحذف بنجاح 🗑️');
    }
}