@extends('adminlte::page')

@section('title', 'SmartCare | لوحة التحكم')

@section('content_header')
    <h1>نظرة عامة على النظام</h1>
@stop

@section('content')
    <div class="row">
        
        {{-- صندوق الأطباء - تم الربط بنجاح --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info shadow">
                <div class="inner">
                    <h3>{{ $stats['doctors'] }}</h3>
                    <p>إجمالي الأطباء</p>
                </div>
                <div class="icon"><i class="fas fa-user-md"></i></div>
                <a href="{{ url('panel/doctors') }}" class="small-box-footer">
                    عرض التفاصيل <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- صندوق المرضى - تم الربط بنجاح --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success shadow">
                <div class="inner">
                    <h3>{{ $stats['patients'] }}</h3>
                    <p>إجمالي المرضى</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ url('panel/patients') }}" class="small-box-footer">
                    عرض التفاصيل <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- صندوق المواعيد - سيتم ربطه بجدول المواعيد --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning shadow text-white">
                <div class="inner text-white">
                    <h3 class="text-white">{{ $stats['pending'] }}</h3>
                    <p>مواعيد بانتظار التأكيد</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-check text-white-50"></i></div>
                <a href="{{ url('panel/appointments') }}" class="small-box-footer" style="color: rgba(255,255,255,0.8) !important;">
                    عرض المواعيد <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- صندوق العيادات - تعديل الرابط الأحمر ليعمل الآن --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger shadow">
                <div class="inner">
                    <h3>{{ $stats['clinics'] }}</h3>
                    <p>العيادات المشتركة</p>
                </div>
                <div class="icon"><i class="fas fa-hospital"></i></div>
                {{-- التعديل الأساسي: ربط الزر بصفحة العيادات --}}
                <a href="{{ url('panel/clinics') }}" class="small-box-footer">
                    عرض العيادات <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

    </div>
@stop