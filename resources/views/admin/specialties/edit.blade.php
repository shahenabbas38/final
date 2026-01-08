@extends('adminlte::page')

@section('title', 'تعديل تخصص | SmartCare')

@section('content_header')
    <h1>تعديل تخصص طبي</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-outline card-info shadow">
            <div class="card-header">
                <h3 class="card-title">تحديث اسم التخصص</h3>
            </div>
            <form action="{{ route('admin.specialties.update', $specialty->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">الاسم الحالي</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $specialty->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <button type="submit" class="btn btn-info px-4">تحديث</button>
                    <a href="{{ route('admin.specialties.index') }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop