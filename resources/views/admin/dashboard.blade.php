@extends('adminlte::page')

@section('title', 'SmartCare | لوحة التحكم')

@section('content_header')
    <h1>نظرة عامة على النظام</h1>
@stop

@section('content')
    <div class="row">
        
        {{-- العمود الأول (الجهة اليمنى) --}}
        <div class="col-md-6">
            {{-- 1. صندوق الأطباء --}}
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

            {{-- 2. صندوق التخصصات (اللون البنفسجي) --}}
            <div class="small-box bg-purple shadow">
                <div class="inner">
                    <h3>{{ $stats['specialties'] }}</h3>
                    <p>التخصصات الطبية</p>
                </div>
                <div class="icon"><i class="fas fa-stethoscope"></i></div>
                <a href="{{ route('admin.specialties.index') }}" class="small-box-footer">
                    إدارة التخصصات <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>

            {{-- 3. صندوق العيادات --}}
            <div class="small-box bg-danger shadow">
                <div class="inner">
                    <h3>{{ $stats['clinics'] }}</h3>
                    <p>العيادات المشتركة</p>
                </div>
                <div class="icon"><i class="fas fa-hospital"></i></div>
                <a href="{{ url('panel/clinics') }}" class="small-box-footer">
                    عرض العيادات <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        {{-- العمود الثاني (الجهة اليسرى) --}}
        <div class="col-md-6">
            {{-- 4. صندوق المرضى --}}
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

            {{-- 5. صندوق المواعيد --}}
            <div class="small-box bg-warning shadow text-white">
                <div class="inner">
                    <h3 class="text-white">{{ $stats['pending'] }}</h3>
                    <p class="text-white">مواعيد بانتظار التأكيد</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-check text-white-50"></i></div>
                <a href="{{ url('panel/appointments') }}" class="small-box-footer" style="color: white !important;">
                    عرض المواعيد <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>

            {{-- 6. صندوق تقارير التغذية (اللون النيلي) --}}
            <div class="small-box bg-indigo shadow text-white">
                <div class="inner text-white">
                    <h3>AI</h3>
                    <p>تقارير التغذية الذكية</p>
                </div>
                <div class="icon"><i class="fas fa-utensils text-white-50"></i></div>
                <a href="{{ route('admin.nutrition.reports') }}" class="small-box-footer" style="color: white !important;">
                    عرض التقارير <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

    </div>
@stop

@section('css')
    <style>
        /* تعريف الألوان الإضافية لتتوافق مع AdminLTE */
        .bg-purple { background-color: #6f42c1 !important; color: #fff !important; }
        .bg-indigo { background-color: #6610f2 !important; color: #fff !important; }
        .small-box h3 { font-weight: 700; }
    </style>
@stop