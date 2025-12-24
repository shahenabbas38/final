<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// استدعاء الموديلات الخاصة بجداولك
use App\Models\User;
use App\Models\Appointment;
use App\Models\Clinic;

class DashboardController extends Controller
{
    public function index()
    {
        // جلب الإحصائيات من الجداول (قاعدة البيانات الخاصة بك)
        $stats = [
            'doctors'  => User::where('role', 'DOCTOR')->count(),
            'patients' => User::where('role', 'PATIENT')->count(),
            'pending'  => Appointment::where('status', 'PENDING')->count(),
            'clinics'  => Clinic::count(),
        ];

        // إرسال البيانات لصفحة الـ Blade التي سننشئها بعد قليل
        return view('admin.dashboard', compact('stats'));
    }
}