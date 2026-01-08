<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminWebAuthController;
use App\Http\Controllers\SpecialtyWebController; // الكنترولر الجديد
use App\Http\Controllers\NutritionRecommendationController; // كنترولر التغذية
use App\Models\User;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

// مجموعة روابط لوحة التحكم (تبدأ بـ /panel)
Route::group(['prefix' => 'panel'], function () {

    // 1. روابط عامة (بدون تسجيل دخول)
    Route::get('/login', [AdminWebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminWebAuthController::class, 'login']);
    Route::get('/register', [AdminWebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AdminWebAuthController::class, 'register']);

    // 2. الروابط المحمية (تتطلب تسجيل دخول)
    Route::middleware(['auth'])->group(function () {
        
        // الداش بورد
        Route::get('/dashboard', function () {
            $stats = [
                'doctors'  => User::where('role', 'DOCTOR')->count(),
                'patients' => User::where('role', 'PATIENT')->count(),
                'clinics'  => DB::table('clinics')->count(),
                'pending'  => DB::table('appointments')->where('status', 'PENDING')->count(),
            ];
            return view('admin.dashboard', compact('stats'));
        })->name('admin.dashboard');

        // --- إدارة الأطباء ---
        Route::get('/doctors', [AdminWebAuthController::class, 'indexDoctors'])->name('admin.doctors.index');
        Route::get('/doctors/create', [AdminWebAuthController::class, 'createDoctor'])->name('admin.doctors.create');
        Route::post('/doctors/store', [AdminWebAuthController::class, 'storeDoctor'])->name('admin.doctors.store');
        Route::delete('/doctors/{id}', [AdminWebAuthController::class, 'destroyDoctor'])->name('admin.doctors.destroy');

        // --- إدارة المرضى ---
        Route::get('/patients', [AdminWebAuthController::class, 'indexPatients'])->name('admin.patients.index');
        Route::get('/patients/create', [AdminWebAuthController::class, 'createPatient']);
        Route::post('/patients/store', [AdminWebAuthController::class, 'storePatient']);
        Route::delete('/patients/{id}', [AdminWebAuthController::class, 'destroyPatient']);

        // --- إدارة العيادات ---
        Route::get('/clinics', [AdminWebAuthController::class, 'indexClinics']);
        Route::get('/clinics/create', [AdminWebAuthController::class, 'createClinic']);
        Route::post('/clinics/store', [AdminWebAuthController::class, 'storeClinic']);
        Route::delete('/clinics/{id}', [AdminWebAuthController::class, 'destroyClinic']);

        // --- إدارة المواعيد ---
        Route::get('/appointments', [AdminWebAuthController::class, 'indexAppointments']);
        Route::get('/appointments/update/{id}/{status}', [AdminWebAuthController::class, 'updateAppointmentStatus']);

        // --- الإضافات الجديدة (إدارة التخصصات والتقارير الغذائية) ---
        Route::resource('specialties', SpecialtyWebController::class)->names('admin.specialties');
        Route::get('nutrition-reports', [NutritionRecommendationController::class, 'adminIndex'])->name('admin.nutrition.reports');

        // تسجيل الخروج
        Route::post('/logout', [AdminWebAuthController::class, 'logout'])->name('logout');
    });
    
});