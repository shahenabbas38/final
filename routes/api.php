<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DoctorAuthController;
use App\Http\Controllers\DoctorProfileController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientAuthController;
use App\Http\Controllers\PatientProfileController;
use App\Http\Controllers\AppointmentReminderController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\DashboardMedicalRecordsController;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\MedicationScheduleController;
use App\Http\Controllers\LabTestController;
use App\Http\Controllers\VitalReadingController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\NutritionRecommendationController;
use App\Http\Controllers\AdminWebAuthController;



/*
|--------------------------------------------------------------------------
| API Routes - Doctor User
|--------------------------------------------------------------------------
*/

// 🩺 تسجيل طبيب جديد (USER فقط)
Route::post('/doctor/register', [DoctorAuthController::class, 'register']);

// 🔐 تسجيل دخول الطبيب
Route::post('/doctor/login', [DoctorAuthController::class, 'login']);

// 🔒 مسارات محمية بـ Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // 🚪 تسجيل خروج الطبيب
    Route::post('/doctor/logout', [DoctorAuthController::class, 'logout']);

    // 👤 عرض بيانات المستخدم (User info فقط)
    Route::get('/doctor/user', [DoctorAuthController::class, 'profile']);

    // 📋 عرض جميع الأطباء (User info فقط)
    Route::get('/doctors', [DoctorAuthController::class, 'index']);

    // ✏️ تحديث بيانات المستخدم
    Route::put('/doctor/update/{id}', [DoctorAuthController::class, 'update']);

    // 🗑️ حذف المستخدم
    Route::delete('/doctor/delete/{id}', [DoctorAuthController::class, 'destroy']);

    // ✅ اختبار الاتصال
    Route::get('/ping', function (Request $request) {
        return response()->json([
            'message' => '✅ API connection successful',
            'user' => $request->user(),
        ]);
    });

    /*************** DOCTOR PROFILE *********************************/
    Route::prefix('doctor')->group(function () {
        // 👁️ عرض بروفايل الطبيب الحالي
        Route::get('/profile/details', [DoctorProfileController::class, 'showMyProfile']);

        // ✏️ تحديث أو إنشاء بروفايل الطبيب الحالي
        Route::post('/profile/update', [DoctorProfileController::class, 'updateMyProfile']);

        // 🧾 عرض جميع البروفايلات (إداري)
        Route::get('/profiles', [DoctorProfileController::class, 'index']);

        // 🗑️ حذف بروفايل طبيب
        Route::delete('/profile/{id}', [DoctorProfileController::class, 'destroy']);
        // ✅ السطر الجديد: جلب المرضى الخاصين بالطبيب فقط
        Route::get('/my-patients', [DoctorProfileController::class, 'getMyPatients']);
    });

    /*************** CLINICS *********************************/
    Route::get('/clinics', [ClinicController::class, 'index']);
    Route::post('/clinics', [ClinicController::class, 'store']);
    Route::get('/clinics/{id}', [ClinicController::class, 'show']);
    Route::put('/clinics/{id}', [ClinicController::class, 'update']);
    Route::delete('/clinics/{id}', [ClinicController::class, 'destroy']);

    /*************** SPECIALTIES *********************************/
    Route::get('/specialties', [SpecialtyController::class, 'index']);
    Route::post('/specialties', [SpecialtyController::class, 'store']);
    Route::delete('/specialties/{id}', [SpecialtyController::class, 'destroy']);

    /*************** APPOINTMENTS *********************************/


Route::middleware('auth:sanctum')->group(function () {

    // Appointments
    Route::get('/my-appointments', [AppointmentController::class, 'getPatientAppointments']);
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::put('/appointments/{id}', [AppointmentController::class, 'update']);
    Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);
    /***مشان نحسب ععدد المرضى التابعين للدكتور */
    Route::get('/doctor/patients-count', [AppointmentController::class, 'getDoctorPatientsCount'])->middleware('auth:sanctum');
    // Appointment Reminders ⏰
    Route::post('/reminders', [AppointmentReminderController::class, 'store']);
    Route::get('/reminders', [AppointmentReminderController::class, 'index']);
    Route::delete('/reminders/{id}', [AppointmentReminderController::class, 'destroy']);
});


    /*************** CHAT *********************************/
    Route::post('/chat/conversations', [ChatController::class, 'createConversation']);
    Route::post('/chat/conversations/{id}/members', [ChatController::class, 'addMember']);
    Route::post('/chat/messages', [ChatController::class, 'sendMessage']);
    Route::get('/chat/conversations', [ChatController::class, 'myConversations']);
    Route::get('/chat/conversations/{id}/messages', [ChatController::class, 'getMessages']);
    Route::post('/chat/messages/{id}/seen', [ChatController::class, 'markAsSeen']);
    // رسائل المريض غير المقروءة
    Route::get('/patient/unseen-messages', [ChatController::class, 'getPatientUnseenMessages']);
    // رسائل الطبيب غير المقروءة
    Route::get('/doctor/unseen-messages', [ChatController::class, 'getDoctorUnseenMessages']);

    /*************** DASHBOARD *********************************/
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

    /*************** PATIENT PROFILE (محمي) *********************************/
    Route::get('/patient/profile/details', [PatientProfileController::class, 'show']);
    Route::put('/patient/profile', [PatientProfileController::class, 'update']);
    Route::get('/patient/profiles', [PatientProfileController::class, 'index']); // اختياري إداري
});

/*************** PATIENT AUTH (غير محمي) *********************************/
// 🧍‍♂️ تسجيل مريض جديد
Route::post('/patient/register', [PatientAuthController::class, 'register']);

// 🔐 تسجيل دخول المريض
Route::post('/patient/login', [PatientAuthController::class, 'login']);

/*************** PATIENT AUTH (محمي) *********************************/
// 👤 عرض بيانات حساب المريض
Route::get('/patient/profile', [PatientAuthController::class, 'profile'])->middleware('auth:sanctum');

// 🚪 تسجيل خروج المريض
Route::post('/patient/logout', [PatientAuthController::class, 'logout'])->middleware('auth:sanctum');

// 🧾 عرض كل المرضى (إداري)
Route::get('/patients', [PatientAuthController::class, 'index'])->middleware('auth:sanctum');
/****************************** MedicalRecord *********************************/


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/medical-records', [MedicalRecordController::class, 'index']);
    Route::post('/medical-records', [MedicalRecordController::class, 'store']);
    Route::get('/medical-records/{id}', [MedicalRecordController::class, 'show']);
    Route::put('/medical-records/{id}', [MedicalRecordController::class, 'update']);
    Route::delete('/medical-records/{id}', [MedicalRecordController::class, 'destroy']);
});
    /*************** DASHBOARD *********************************/

Route::middleware('auth:sanctum')->group(function () {
    // 📥 جلب السجلات الخاصة بالمستخدم (مريض/طبيب/أدمن)
    Route::get('/medical-records/my', [DashboardMedicalRecordsController::class, 'myMedicalRecords']);

    // ➕ (اختياري للاختبار) إنشاء سجل طبي — للطبيب فقط
    Route::post('/medical-records', [DashboardMedicalRecordsController::class, 'store']);
});
    /*************** Diagnoses *********************************/

Route::middleware('auth:sanctum')->group(function () {
    // 📥 عرض جميع التشخيصات للمستخدم حسب دوره
    Route::get('/diagnoses', [DiagnosisController::class, 'index']);

    // ➕ إنشاء تشخيص جديد (طبيب فقط)
    Route::post('/diagnoses', [DiagnosisController::class, 'store']);

    // 🗑️ حذف تشخيص (طبيب فقط)
    Route::delete('/diagnoses/{id}', [DiagnosisController::class, 'destroy']);
});
    /*************** prescriptions *********************************/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-prescriptions', [PrescriptionController::class, 'getMyPrescriptions']);
    Route::get('/prescriptions', [PrescriptionController::class, 'index']);
    Route::post('/prescriptions', [PrescriptionController::class, 'store']);
    Route::get('/prescriptions/{id}', [PrescriptionController::class, 'show']);
    Route::delete('/prescriptions/{id}', [PrescriptionController::class, 'destroy']);
});
    /*************** medications *********************************/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/medications', [MedicationController::class, 'index']);
    Route::post('/medications', [MedicationController::class, 'store']);
    Route::get('/medications/{id}', [MedicationController::class, 'show']);
    Route::put('/medications/{id}', [MedicationController::class, 'update']);
    Route::delete('/medications/{id}', [MedicationController::class, 'destroy']);
});
    /*************** medication-schedules *********************************/

// جميع هذه المسارات تتطلب Token (المريض يجب أن يكون مسجلاً للدخول)
Route::middleware('auth:sanctum')->group(function () {

    // 1. جلب جميع جداول المريض (الخاصة بصاحب التوكن فقط)
    Route::get('/medication-schedules', [MedicationScheduleController::class, 'index']);

    // 2. إنشاء جدول دواء جديد (سيتم ربطه تلقائياً بالمريض من خلال التوكن)
    Route::post('/medication-schedules', [MedicationScheduleController::class, 'store']);

    // 3. عرض تفاصيل جدول معين (يجب أن يخص المريض صاحب التوكن)
    Route::get('/medication-schedules/{id}', [MedicationScheduleController::class, 'show']);

    // 4. تعديل جدول دواء (يخص المريض الحالي فقط)
    Route::put('/medication-schedules/{id}', [MedicationScheduleController::class, 'update']);

    // 5. حذف جدول دواء مع جرعاته
    Route::delete('/medication-schedules/{id}', [MedicationScheduleController::class, 'destroy']);

    // 6. تحديث حالة جرعة محددة (تم أخذها/لم تُؤخذ)
    Route::put('/medication-intakes/{id}', [MedicationScheduleController::class, 'updateIntake']);
    
});
    /***************LabTest *********************************/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-lab-tests', [LabTestController::class, 'getMyLabTests']);
    Route::get('/lab-tests', [LabTestController::class, 'index']);
    Route::post('/lab-tests', [LabTestController::class, 'store']);
    Route::get('/lab-tests/{id}', [LabTestController::class, 'show']);
    Route::put('/lab-tests/{id}', [LabTestController::class, 'update']);
    Route::delete('/lab-tests/{id}', [LabTestController::class, 'destroy']);

    // ➕ إضافة نتيجة لتحليل
    Route::post('/lab-tests/{id}/results', [LabTestController::class, 'addResult']);
});
    /***************VitalReading *********************************/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/vital-readings', [VitalReadingController::class, 'index']);
    Route::post('/vital-readings', [VitalReadingController::class, 'store']);
    Route::get('/vital-readings/{id}', [VitalReadingController::class, 'show']);
    Route::put('/vital-readings/{id}', [VitalReadingController::class, 'update']);
    Route::delete('/vital-readings/{id}', [VitalReadingController::class, 'destroy']);
});
    /***************Raiting *********************************/
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/ratings', [RatingController::class, 'index']);
    Route::post('/ratings', [RatingController::class, 'store']);
    Route::get('/ratings/{id}', [RatingController::class, 'show']);
    Route::delete('/ratings/{id}', [RatingController::class, 'destroy']);
});
    /***************Notification*************************************/

Route::middleware('auth:sanctum')->group(function () {
    // 📬 الإشعارات
    Route::get('/notifications', [NotificationController::class, 'index']);            // عرض كل الإشعارات
    Route::post('/notifications', [NotificationController::class, 'store']);           // إنشاء إشعار
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']); // تعليم كمقروء
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);  // حذف إشعار
});
    /***************Admin*************************************/

// Route::prefix('admin')->group(function () {
//     Route::post('/register', [AdminAuthController::class, 'register']);  // يمكن حذفه إذا التسجيل فقط من طرف النظام
//     Route::post('/login', [AdminAuthController::class, 'login']);
//     Route::middleware(['auth:sanctum'])->group(function () {
//         Route::post('/logout', [AdminAuthController::class, 'logout']);
//         Route::get('/profile', [AdminAuthController::class, 'profile']);
//         Route::get('/all', [AdminAuthController::class, 'index']);
//     });
// });


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/nutrition/recommendations', [NutritionRecommendationController::class, 'index']); // عرض الكل
    Route::post('/nutrition/recommendations/generate', [NutritionRecommendationController::class, 'store']); // توليد جديد
    Route::get('/nutrition/my-plan', [NutritionRecommendationController::class, 'getMyRecommendations']); // ✅ خطة المريض الحالية
});
