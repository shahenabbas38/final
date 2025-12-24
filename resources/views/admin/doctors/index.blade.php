@extends('adminlte::page')

@section('title', 'إدارة الأطباء | SmartCare')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark"><i class="fas fa-user-md text-primary"></i> إدارة الأطباء</h1>
        <a href="{{ route('admin.doctors.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle"></i> إضافة طبيب جديد
        </a>
    </div>
@stop

@section('content')
<div class="card card-outline card-primary shadow">
    <div class="card-header bg-white">
        <h3 class="card-title text-bold"><i class="fas fa-list text-secondary"></i> سجل البيانات</h3>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-valign-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 20%">الاسم الكامل</th>
                        <th>التخصص</th>
                        <th>العيادة</th>
                        <th>رقم الهاتف</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-center" style="width: 15%">العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($doctors as $doctor)
                        <tr>
                            <td class="text-bold text-primary">
                                {{ $doctor->doctorProfile->full_name ?? 'غير محدد' }}
                            </td>
                            <td>
                                <span class="badge badge-info shadow-sm">
                                    {{ $doctor->doctorProfile->specialty->name ?? '---' }}
                                </span>
                            </td>
                            <td>
                                <i class="fas fa-hospital-alt text-muted mr-1"></i>
                                {{ $doctor->doctorProfile->clinic->name ?? '---' }}
                            </td>
                            <td>
                                <a href="tel:{{ $doctor->phone }}" class="text-secondary text-sm">
                                    <i class="fas fa-phone-alt mr-1"></i> {{ $doctor->phone }}
                                </a>
                            </td>
                            <td class="text-center">
                                @if($doctor->status == 'ACTIVE')
                                    <span class="badge badge-success px-3 py-2 shadow-xs">نشط</span>
                                @else
                                    <span class="badge badge-danger px-3 py-2 shadow-xs">غير نشط</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{-- زر الحذف بشكل أنيق --}}
                                <form action="{{ route('admin.doctors.destroy', $doctor->id) }}" method="POST" 
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا الطبيب نهائياً من النظام؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm shadow-sm rounded-pill px-3">
                                        <i class="fas fa-trash-alt mr-1"></i> حذف
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-folder-open text-muted fa-3x mb-3 d-block"></i>
                                <p class="text-muted">لا يوجد أطباء مسجلين حالياً في النظام</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- تنبيهات النجاح --}}
@if(session('success'))
    <script>
        $(document).ready(function() {
            toastr.success('{{ session("success") }}');
        });
    </script>
@endif

@stop

@section('css')
    <style>
        .table-valign-middle td { vertical-align: middle !important; }
        .shadow-xs { box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important; }
        .card { border-radius: 12px; }
        .badge { font-weight: 500; font-size: 0.85rem; }
    </style>
@stop