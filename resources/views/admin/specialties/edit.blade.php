@extends('adminlte::page')

@section('title', 'تعديل تخصص | SmartCare')

{{-- التعديل هنا: إضافة الأيقونة وتغيير اللون ليتناسب مع قسم التعديل --}}
@section('content_header')
    <h1><i class="fas fa-edit text-info"></i> تعديل تخصص طبي</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        {{-- كرت بتصميم محدد لتمييز عملية التعديل --}}
        <div class="card card-outline card-info shadow">
            <div class="card-header">
                <h3 class="card-title text-bold">تحديث بيانات التخصص</h3>
            </div>
            
            {{-- التأكد من إرسال البيانات إلى مسار الـ update باستخدام طريقة PUT --}}
            <form action="{{ route('admin.specialties.update', $specialty->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">اسم التخصص الحالي</label>
                        {{-- تم ضبط القيمة الافتراضية لتكون اسم التخصص القادم من قاعدة البيانات --}}
                        <input type="text" name="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $specialty->name) }}" 
                               placeholder="أدخل اسم التخصص الجديد" required>
                        
                        {{-- عرض رسائل الخطأ في حال وجود تعارض أو اسم مكرر --}}
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="card-footer bg-light d-flex justify-content-between">
                    <button type="submit" class="btn btn-info px-4 shadow-sm">
                        <i class="fas fa-save mr-1"></i> حفظ التغييرات
                    </button>
                    <a href="{{ route('admin.specialties.index') }}" class="btn btn-secondary shadow-sm">
                        إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        .card { border-radius: 12px; }
        .form-control { border-radius: 8px; }
        .btn { border-radius: 8px; }
    </style>
@stop