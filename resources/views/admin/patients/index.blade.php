@extends('adminlte::page')

@section('title', 'إدارة المرضى | SmartCare')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark"><i class="fas fa-users text-success"></i> إدارة المرضى</h1>
        {{-- زر إضافة مريض جديد مفعل الآن --}}
        <a href="{{ url('panel/patients/create') }}" class="btn btn-success shadow-sm">
            <i class="fas fa-plus-circle"></i> إضافة مريض جديد
        </a>
    </div>
@stop

@section('content')
<div class="card card-outline card-success shadow">
    <div class="card-header bg-white">
        <h3 class="card-title text-bold"><i class="fas fa-list text-secondary"></i> قائمة المرضى المسجلين</h3>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-valign-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>اسم المريض</th>
                        <th>الجنس</th>
                        <th>تاريخ الميلاد</th>
                        <th>الطول/الوزن</th>
                        <th>الحالة الصحية</th>
                        <th>رقم الهاتف</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-center">العمليات</th> {{-- عمود العمليات الجديد --}}
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                        <tr>
                            <td class="text-bold text-success">
                                {{ $patient->patientProfile->full_name ?? 'غير محدد' }}
                            </td>
                            <td>{{ $patient->patientProfile->gender ?? '---' }}</td>
                            <td>{{ $patient->patientProfile->dob ?? '---' }}</td>
                            <td>
                                <small class="badge badge-light border">
                                    {{ $patient->patientProfile->height_cm ?? '--' }} سم / 
                                    {{ $patient->patientProfile->weight_kg ?? '--' }} كغ
                                </small>
                            </td>
                            <td>
                                <span class="badge badge-warning text-white">
                                    <i class="fas fa-notes-medical mr-1"></i>
                                    {{ $patient->patientProfile->primary_condition ?? 'عادي' }}
                                </span>
                            </td>
                            <td>
                                <i class="fas fa-phone-alt text-muted mr-1 small"></i>
                                {{ $patient->phone }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $patient->status == 'ACTIVE' ? 'badge-success' : 'badge-danger' }} px-3">
                                    {{ $patient->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                {{-- نموذج الحذف الأنيق --}}
                                <form action="{{ url('panel/patients/'.$patient->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المريض نهائياً؟');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill shadow-sm px-3">
                                        <i class="fas fa-trash-alt mr-1"></i> حذف
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-user-slash text-muted fa-3x mb-3 d-block"></i>
                                <p class="text-muted">لا يوجد مرضى مسجلين حالياً في النظام</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- عرض رسائل النجاح --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm mt-3" role="alert">
        <i class="icon fas fa-check"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
@stop

@section('css')
<style>
    .table-valign-middle td { vertical-align: middle !important; }
    .card { border-radius: 12px; }
    .badge { font-weight: 500; }
</style>
@stop