@extends('adminlte::page')
@section('title', 'إدارة العيادات')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-hospital text-danger"></i> العيادات المشتركة</h1>
        <a href="{{ url('panel/clinics/create') }}" class="btn btn-danger shadow-sm">إضافة عيادة جديدة</a>
    </div>
@stop

@section('content')
<div class="card card-outline card-danger shadow">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th>اسم العيادة</th>
                    <th>العنوان / الموقع</th>
                    <th class="text-center">العمليات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clinics as $clinic)
                <tr>
                    <td class="text-bold">{{ $clinic->name }}</td>
                    <td>{{ $clinic->address }}</td>
                    <td class="text-center">
                        <form action="{{ url('panel/clinics/'.$clinic->id) }}" method="POST" onsubmit="return confirm('حذف العيادة؟');">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm rounded-pill">حذف</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop