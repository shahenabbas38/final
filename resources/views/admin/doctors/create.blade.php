@extends('adminlte::page')

@section('title', 'إضافة طبيب جديد | SmartCare')

@section('content_header')
    <h1><i class="fas fa-user-plus text-primary"></i> إضافة طبيب جديد للنظام</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-primary card-outline shadow">
            <div class="card-header">
                <h3 class="card-title text-bold">معلومات الحساب والملف الشخصي</h3>
            </div>
            
            {{-- نموذج الإرسال --}}
            <form action="{{ route('admin.doctors.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        {{-- الاسم الكامل --}}
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="full_name">الاسم الكامل</label>
                                <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" 
                                       placeholder="أدخل الاسم الثلاثي للطبيب" value="{{ old('full_name') }}" required>
                            </div>
                        </div>

                        {{-- البريد الإلكتروني --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                       placeholder="email@example.com" value="{{ old('email') }}" required>
                            </div>
                        </div>

                        {{-- رقم الهاتف --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">رقم الهاتف</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                       placeholder="09xxxxxxxx" value="{{ old('phone') }}" required>
                            </div>
                        </div>

                        {{-- كلمة المرور --}}
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="password">كلمة المرور المؤقتة</label>
                                <input type="password" name="password" class="form-control" placeholder="********" required>
                                <small class="text-muted">يجب ألا تقل عن 8 محارف.</small>
                            </div>
                        </div>

                        <hr class="w-100">

                        {{-- اختيار التخصص --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>التخصص الطبي</label>
                                <select name="specialty_id" class="form-control select2 shadow-sm" required>
                                    <option value="">اختر التخصص...</option>
                                    @foreach($specialties as $specialty)
                                        <option value="{{ $specialty->id }}">{{ $specialty->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- اختيار العيادة --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>المركز الطبي / العيادة</label>
                                <select name="clinic_id" class="form-control select2 shadow-sm" required>
                                    <option value="">اختر العيادة...</option>
                                    @foreach($clinics as $clinic)
                                        <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light text-right">
                    <a href="{{ route('admin.doctors.index') }}" class="btn btn-secondary">إلغاء</a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="fas fa-save mr-1"></i> حفظ بيانات الطبيب
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        .card { border-radius: 15px; }
        .form-control { border-radius: 8px; }
        label { font-weight: 600 !important; color: #495057; }
    </style>
@stop