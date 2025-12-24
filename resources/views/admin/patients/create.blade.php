@extends('adminlte::page')

@section('title', 'إضافة مريض جديد | SmartCare')

@section('content_header')
    <h1><i class="fas fa-user-plus text-success"></i> إضافة مريض جديد</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-success card-outline shadow mt-2">
            <div class="card-header">
                <h3 class="card-title text-bold">بيانات حساب المريض الجديد</h3>
            </div>
            
            <form action="{{ url('panel/patients/store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        {{-- الاسم الكامل --}}
                        <div class="col-md-12 form-group">
                            <label>الاسم الكامل</label>
                            <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" 
                                   value="{{ old('full_name') }}" placeholder="أدخل اسم المريض الثلاثي" required>
                            @error('full_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- البريد الإلكتروني --}}
                        <div class="col-md-6 form-group">
                            <label>البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}" placeholder="example@mail.com" required>
                            @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- رقم الهاتف --}}
                        <div class="col-md-6 form-group">
                            <label>رقم الهاتف</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone') }}" placeholder="09xxxxxxxx" required>
                            @error('phone') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- كلمة المرور --}}
                        <div class="col-md-12 form-group">
                            <label>كلمة المرور</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="********" required>
                            <small class="text-muted">ستُستخدم لتسجيل دخول المريض لاحقاً.</small>
                            @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light d-flex justify-content-between">
                    <a href="{{ url('panel/patients') }}" class="btn btn-default">
                        <i class="fas fa-times"></i> إلغاء
                    </a>
                    <button type="submit" class="btn btn-success px-5 shadow-sm">
                        <i class="fas fa-save mr-1"></i> حفظ بيانات المريض
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
    label { font-weight: 600 !important; color: #2d3436; }
</style>
@stop