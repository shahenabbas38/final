@extends('adminlte::page')

@section('title', 'إدارة التخصصات | SmartCare')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-stethoscope text-primary"></i> التخصصات الطبية</h1>
        <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#addSpecialtyModal">
            <i class="fas fa-plus-circle"></i> إضافة تخصص جديد
        </button>
    </div>
@stop

@section('content')
<div class="card card-outline card-primary shadow">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 10%">#ID</th>
                        <th>اسم التخصص</th>
                        <th>تاريخ الإضافة</th>
                        <th class="text-center">العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($specialties as $specialty)
                        <tr>
                            <td>{{ $specialty->id }}</td>
                            <td class="text-bold text-primary">{{ $specialty->name }}</td>
                            <td>{{ $specialty->created_at->format('Y-m-d') }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.specialties.edit', $specialty->id) }}" class="btn btn-sm btn-outline-info rounded-pill px-3">
                                    <i class="fas fa-edit"></i> تعديل
                                </a>
                                <form action="{{ route('admin.specialties.destroy', $specialty->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا التخصص؟ سيؤثر ذلك على الأطباء المرتبطين به.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                        <i class="fas fa-trash"></i> حذف
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">لا يوجد تخصصات مضافة حالياً</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal للإضافة السريعة --}}
<div class="modal fade" id="addSpecialtyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 15px;">
            <form action="{{ route('admin.specialties.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">إضافة تخصص جديد</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>اسم التخصص الطبي</label>
                        <input type="text" name="name" class="form-control" placeholder="مثلاً: جراحة قلب، طب أطفال..." required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary px-4">حفظ البيانات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop