<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminWebAuthController extends Controller
{
    // 1️⃣ عرض صفحة الدخول
    public function showLogin() {
        return view('login'); 
    }

    // 2️⃣ تنفيذ الدخول
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();
            return redirect()->intended('/panel/dashboard');
        }

        return back()->withErrors(['email' => 'البيانات المدخلة غير صحيحة.']);
    }

    // 3️⃣ عرض صفحة التسجيل (للمدير)
    public function showRegister() {
        return view('register');
    }

    // 4️⃣ تنفيذ تسجيل المدير
    public function register(Request $request) {
        $request->validate([
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|unique:users,phone',
            'password' => 'required|min:8',
        ]);

        $user = User::create([
            'email'         => $request->email,
            'phone'         => $request->phone,
            'password_hash' => Hash::make($request->password), 
            'role'          => 'ADMIN',
            'status'        => 'ACTIVE',
        ]);

        Auth::login($user);
        return redirect('/panel/dashboard');
    }

    // ============================================================
    // إدارة الأطباء (DOCTORS MANAGEMENT)
    // ============================================================

    // 5️⃣ عرض قائمة الأطباء
    public function indexDoctors()
    {
        $doctors = User::where('role', 'DOCTOR')
            ->with(['doctorProfile.specialty', 'doctorProfile.clinic']) 
            ->get();

        return view('admin.doctors.index', compact('doctors'));
    }

    // 6️⃣ عرض صفحة إضافة طبيب جديد
    public function createDoctor()
    {
        $specialties = DB::table('specialties')->get();
        $clinics = DB::table('clinics')->get();
        return view('admin.doctors.create', compact('specialties', 'clinics'));
    }

    // 7️⃣ حفظ الطبيب الجديد في قاعدتي البيانات (users & doctor_profiles)
    public function storeDoctor(Request $request)
    {
        $request->validate([
            'full_name'    => 'required|string|max:200',
            'email'        => 'required|email|unique:users,email',
            'phone'        => 'required|unique:users,phone',
            'password'     => 'required|min:8',
            'specialty_id' => 'required|exists:specialties,id',
            'clinic_id'    => 'required|exists:clinics,id',
        ]);

        DB::transaction(function () use ($request) {
            // إنشاء المستخدم أولاً
            $user = User::create([
                'email'         => $request->email,
                'phone'         => $request->phone,
                'password_hash' => Hash::make($request->password),
                'role'          => 'DOCTOR',
                'status'        => 'ACTIVE',
            ]);

            // إنشاء بروفايل الطبيب وربطه بالمستخدم
            DB::table('doctor_profiles')->insert([
                'user_id'              => $user->id,
                'full_name'            => $request->full_name,
                'primary_specialty_id' => $request->specialty_id,
                'clinic_id'            => $request->clinic_id,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        });

        return redirect()->route('admin.doctors.index')->with('success', 'تم إضافة الطبيب بنجاح');
    }

    // 8️⃣ حذف الطبيب نهائياً
    public function destroyDoctor($id)
    {
        $user = User::findOrFail($id);
        
        // التأكد من أنه طبيب قبل الحذف لزيادة الأمان
        if ($user->role === 'DOCTOR') {
            $user->delete(); // سيحذف البروفايل تلقائياً بسبب ON DELETE CASCADE
            return redirect()->route('admin.doctors.index')->with('success', 'تم حذف الطبيب من النظام');
        }

        return back()->with('error', 'حدث خطأ أثناء الحذف');
    }
    // جلب قائمة المرضى
    public function indexPatients()
    {
        $patients = User::where('role', 'PATIENT')
            ->with('patientProfile') // علاقة مع جدول patient_profiles
            ->get();

        return view('admin.patients.index', compact('patients'));
    }
    // عرض صفحة إضافة مريض
    public function createPatient() {
        return view('admin.patients.create');
    }

    // حفظ المريض الجديد
    public function storePatient(Request $request) {
        $request->validate([
            'full_name' => 'required|string|max:200',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'password' => 'required|min:8',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'email' => $request->email,
                'phone' => $request->phone,
                'password_hash' => Hash::make($request->password),
                'role' => 'PATIENT',
                'status' => 'ACTIVE',
            ]);

            DB::table('patient_profiles')->insert([
                'user_id' => $user->id,
                'full_name' => $request->full_name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect('panel/patients')->with('success', 'تم إضافة المريض بنجاح');
    }

    // حذف المريض
    public function destroyPatient($id) {
        $user = User::findOrFail($id);
        if ($user->role === 'PATIENT') {
            $user->delete(); // سيحذف البروفايل تلقائياً
            return redirect('panel/patients')->with('success', 'تم حذف المريض بنجاح');
        }
        return back();
    }
        // 1. عرض قائمة العيادات
    public function indexClinics() {
        $clinics = DB::table('clinics')->get();
        return view('admin.clinics.index', compact('clinics'));
    }

    // 2. عرض صفحة إضافة عيادة
    public function createClinic() {
        return view('admin.clinics.create');
    }

    // 3. حفظ العيادة الجديدة
    public function storeClinic(Request $request) {
        $request->validate([
            'name' => 'required|string|max:200',
            'address' => 'required|string',
        ]);

        DB::table('clinics')->insert([
            'name' => $request->name,
            'address' => $request->address,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('panel/clinics')->with('success', 'تم إضافة العيادة بنجاح');
    }

    // 4. حذف العيادة
    public function destroyClinic($id) {
        DB::table('clinics')->where('id', $id)->delete();
        return redirect('panel/clinics')->with('success', 'تم حذف العيادة');
    }
        // جلب قائمة المواعيد
    public function indexAppointments() {
        $appointments = DB::table('appointments')
            ->join('patient_profiles', 'appointments.patient_id', '=', 'patient_profiles.user_id')
            ->join('doctor_profiles', 'appointments.doctor_id', '=', 'doctor_profiles.user_id')
            ->join('clinics', 'appointments.clinic_id', '=', 'clinics.id')
            ->select(
                'appointments.*', 
                'patient_profiles.full_name as patient_name', 
                'doctor_profiles.full_name as doctor_name',
                'clinics.name as clinic_name'
            )
            // تم تغيير appointment_time إلى start_at
            ->orderBy('appointments.start_at', 'asc') 
            ->get();

        return view('admin.appointments.index', compact('appointments'));
    }

    // دالة لتغيير حالة الموعد (تأكيد أو إلغاء)
    public function updateAppointmentStatus($id, $status) {
        DB::table('appointments')
            ->where('id', $id)
            ->update(['status' => $status, 'updated_at' => now()]);

        return redirect()->back()->with('success', 'تم تحديث حالة الموعد بنجاح');
    }
    // 9️⃣ تسجيل الخروج
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/panel/login');
    }

}