@extends('adminlte::page')

@section('title', 'إضافة عيادة جديدة | SmartCare')

@section('content_header')
    <h1><i class="fas fa-hospital text-danger"></i> إضافة عيادة جديدة</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-danger card-outline shadow mt-2">
            <div class="card-header">
                <h3 class="card-title text-bold">بيانات العيادة أو المركز الطبي</h3>
            </div>
            
            <form action="{{ url('panel/clinics/store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        {{-- اسم العيادة --}}
                        <div class="col-md-12 form-group">
                            <label>اسم العيادة / المركز</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" placeholder="مثلاً: عيادة الأمل التخصصية" required>
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- عنوان العيادة --}}
                        <div class="col-md-12 form-group">
                            <label>العنوان بالتفصيل</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" 
                                      rows="3" placeholder="المحافظة - المنطقة - الشارع - البناء" required>{{ old('address') }}</textarea>
                            @error('address') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light d-flex justify-content-between">
                    <a href="{{ url('panel/clinics') }}" class="btn btn-default">
                        <i class="fas fa-times"></i> إلغاء
                    </a>
                    <button type="submit" class="btn btn-danger px-5 shadow-sm">
                        <i class="fas fa-save mr-1"></i> حفظ بيانات العيادة
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