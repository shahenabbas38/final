@extends('adminlte::page')
@section('title', 'إدارة المواعيد | SmartCare')

@section('content_header')
    <h1><i class="fas fa-calendar-alt text-warning"></i> جدول المواعيد الطبية</h1>
@stop

@section('content')
<div class="card card-outline card-warning shadow">
    <div class="card-header bg-white">
        <h3 class="card-title text-bold">قائمة المواعيد المحجوزة</h3>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-valign-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>المريض</th>
                        <th>الطبيب</th>
                        <th>العيادة</th>
                        <th>التاريخ والوقت</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-center">العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $app)
                        <tr>
                            <td class="text-bold text-primary">{{ $app->patient_name }}</td>
                            <td>د. {{ $app->doctor_name }}</td>
                            <td><span class="badge badge-light border">{{ $app->clinic_name }}</span></td>
                            {{-- التعديل هنا: استخدام start_at بدلاً من appointment_time --}}
                            <td>{{ date('Y-m-d H:i', strtotime($app->start_at)) }}</td> 
                            <td class="text-center">
                                @if($app->status == 'PENDING')
                                    <span class="badge badge-warning">بانتظار التأكيد</span>
                                @elseif($app->status == 'CONFIRMED')
                                    <span class="badge badge-success px-3">مؤكد</span>
                                @elseif($app->status == 'CANCELLED')
                                    <span class="badge badge-danger">ملغي</span>
                                @else
                                    <span class="badge badge-info">{{ $app->status }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($app->status == 'PENDING')
                                    <a href="{{ url('panel/appointments/update/'.$app->id.'/CONFIRMED') }}" class="btn btn-xs btn-success rounded-pill px-3 shadow-sm">تأكيد</a>
                                    <a href="{{ url('panel/appointments/update/'.$app->id.'/CANCELLED') }}" class="btn btn-xs btn-danger rounded-pill px-3 shadow-sm">إلغاء</a>
                                @else
                                    <span class="text-muted small">لا توجد إجراءات</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">لا توجد مواعيد حالياً</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop