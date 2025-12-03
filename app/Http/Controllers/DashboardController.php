<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DoctorProfile;
use App\Models\PatientProfile;
use App\Models\Clinic;
use App\Models\Appointment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * 📊 إرجاع ملخص كامل للوحة التحكم
     */
    public function summary()
    {
        return response()->json([
            'users_total'               => User::count(),
            'doctors_total'             => DoctorProfile::count(),
            'patients_total'            => PatientProfile::count(),
            'clinics_total'             => Clinic::count(),
            'appointments_pending'      => Appointment::where('status', 'PENDING')->count(),
            'appointments_confirmed'    => Appointment::where('status', 'CONFIRMED')->count(),
            'appointments_completed'    => Appointment::where('status', 'COMPLETED')->count(),
            'appointments_cancelled'    => Appointment::where('status', 'CANCELLED')->count(),
            'latest_users'              => User::latest()->take(5)->get(['id', 'email', 'role', 'created_at']),
            'latest_appointments'       => Appointment::with(['doctor', 'patient', 'clinic'])
                                                    ->latest()
                                                    ->take(5)
                                                    ->get(),
        ]);
    }
}
