@extends('adminlte::page')

@section('title', 'تقارير التغذية الذكية | SmartCare')

@section('content_header')
    <h1><i class="fas fa-robot text-success"></i> توصيات الذكاء الاصطناعي الغذائية</h1>
@stop

@section('content')
<div class="card card-outline card-success shadow">
    <div class="card-header">
        <h3 class="card-title text-bold">سجل الوجبات الموصى بها للمرضى</h3>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>المريض</th>
                        <th>نوع الوجبة</th>
                        <th>اسم الطعام</th>
                        <th class="text-center">السعرات</th>
                        <th>القيم الغذائية (P/C/F)</th>
                        <th>تاريخ التوليد</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recommendations as $rec)
                        <tr>
                            <td class="text-bold text-primary">
                                {{ $rec->patient->full_name ?? 'غير محدد' }}
                            </td>
                            <td>
                                <span class="badge badge-info px-2 py-1">{{ $rec->meal_type }}</span>
                            </td>
                            <td>{{ $rec->food_name }}</td>
                            <td class="text-center">
                                <span class="badge badge-warning">{{ $rec->calories }} kcal</span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    بروتين: <b>{{ $rec->protein }}g</b> | 
                                    كارب: <b>{{ $rec->carbohydrates }}g</b> | 
                                    دهون: <b>{{ $rec->fat }}g</b>
                                </small>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($rec->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-utensils text-muted fa-3x mb-3 d-block"></i>
                                <p class="text-muted">لا يوجد توصيات غذائية مولدة حالياً</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- التعديل هنا: تنسيق أزرار التنقل بشكل احترافي --}}
    @if($recommendations->hasPages())
        <div class="card-footer clearfix">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    <small>
                        عرض السجلات من <b>{{ $recommendations->firstItem() }}</b> إلى <b>{{ $recommendations->lastItem() }}</b> 
                        من أصل <b>{{ $recommendations->total() }}</b> نتيجة
                    </small>
                </div>
                <div class="pagination-sm">
                    {{ $recommendations->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
@stop

@section('css')
    <style>
        .table td { vertical-align: middle !important; }
        .badge { font-weight: 500; font-size: 0.9rem; }
        
        /* تحسين شكل أزرار الترقيم لتبدو كأزرار حقيقية */
        .pagination { margin: 0; }
        .page-item.active .page-link {
            background-color: #28a745; /* لون أخضر متناسق مع قسم التغذية */
            border-color: #28a745;
        }
        .page-link { color: #28a745; }
    </style>
@stop