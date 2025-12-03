<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DoctorAuthController extends Controller
{
    /**
     * 🩺 Register a new doctor (USER ONLY) + generate RSA keys
     */
    public function register(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|regex:/@gmail\.com$/|unique:users,email',
            'phone'    => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8',
        ]);

        // 🧑‍⚕️ إنشاء مستخدم فقط (بدون بروفايل)
        $user = User::create([
            'email'         => $request->email,
            'phone'         => $request->phone,
            'password_hash' => Hash::make($request->password),
            'role'          => 'DOCTOR',
            'status'        => 'ACTIVE',
        ]);

        /**
         * 🔐 توليد مفتاح عام و خاص (RSA 2048)
         */
        $keyPair = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        // استخراج المفتاح الخاص (PEM)
        openssl_pkey_export($keyPair, $privateKeyPem);

        // استخراج المفتاح العام
        $keyDetails = openssl_pkey_get_details($keyPair);
        $publicKeyPem = $keyDetails['key'];

        // حفظ المفتاح العام فقط في قاعدة البيانات
        $user->public_key = $publicKeyPem;
        $user->save();

        // 🧹 حذف أي توكنات قديمة (احتياطيًا)
        $user->tokens()->delete();

        // 🔑 إنشاء توكن واحد فقط
        $token = $user->createToken('doctor_api_token')->plainTextToken;

        return response()->json([
            'message'      => 'Doctor registered successfully ✅',
            'user'         => $user,
            'token'        => $token,
            // ⚠️ المفتاح الخاص يرجع مرة واحدة فقط للمستخدم ويجب تخزينه على الجهاز
            'private_key'  => $privateKeyPem
        ], 201);
    }

    /**
     * 🔐 Doctor Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return response()->json(['message' => 'Invalid credentials ❌'], 401);
        }

        if ($user->role !== 'DOCTOR') {
            return response()->json(['message' => 'Access denied. Doctors only.'], 403);
        }

        if ($user->status !== 'ACTIVE') {
            return response()->json(['message' => 'Your account is not active.'], 403);
        }

        // 🧹 حذف أي توكنات قديمة قبل إنشاء الجديد
        $user->tokens()->delete();

        // 🔑 إنشاء توكن جديد واحد فقط
        $token = $user->createToken('doctor_api_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful ✅',
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    /**
     * 🚪 Logout doctor
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully ✅']);
    }

    /**
     * 👤 Get basic doctor user info
     */
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }

    /**
     * 📋 Get all doctors (only user info)
     */
    public function index()
    {
        $doctors = User::where('role', 'DOCTOR')->get();

        return response()->json([
            'message' => 'All doctors fetched successfully ✅',
            'doctors' => $doctors
        ]);
    }

    /**
     * ✏️ Update user basic info
     */
    public function update(Request $request, $id)
    {
        $user = User::where('id', $id)->where('role', 'DOCTOR')->first();
        if (!$user) {
            return response()->json(['message' => 'Doctor not found ❌'], 404);
        }

        $request->validate([
            'email'    => 'email|regex:/@gmail\.com$/|unique:users,email,' . $user->id,
            'phone'    => 'string|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:8',
            'status'   => 'in:ACTIVE,SUSPENDED,DELETED'
        ]);

        if ($request->filled('email')) $user->email = $request->email;
        if ($request->filled('phone')) $user->phone = $request->phone;
        if ($request->filled('password')) $user->password_hash = Hash::make($request->password);
        if ($request->filled('status')) $user->status = $request->status;

        $user->save();

        return response()->json([
            'message' => 'Doctor user updated successfully ✅',
            'user'    => $user
        ]);
    }

    /**
     * 🗑️ Delete doctor user
     */
    public function destroy($id)
    {
        $user = User::where('id', $id)->where('role', 'DOCTOR')->first();
        if (!$user) {
            return response()->json(['message' => 'Doctor not found ❌'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'Doctor deleted successfully ✅']);
    }
}
