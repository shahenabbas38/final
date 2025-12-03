<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PatientAuthController extends Controller
{
    /**
     * 🧍‍♂️ Register patient (User only) + generate RSA key pair
     */
    public function register(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|regex:/@gmail\.com$/|unique:users,email',
            'phone'    => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8',
        ]);

        // 🧑 إنشاء المستخدم
        $user = User::create([
            'email'         => $request->email,
            'phone'         => $request->phone,
            'password_hash' => Hash::make($request->password),
            'role'          => 'PATIENT',
            'status'        => 'ACTIVE',
        ]);

        /**
         * 🔐 توليد مفاتيح التشفير (Public & Private)
         */
        $keyPair = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        // استخراج المفتاح الخاص PEM
        openssl_pkey_export($keyPair, $privateKeyPem);

        // استخراج المفتاح العام
        $keyDetails = openssl_pkey_get_details($keyPair);
        $publicKeyPem = $keyDetails['key'];

        // 🗂️ تخزين المفتاح العام فقط في قاعدة البيانات
        $user->public_key = $publicKeyPem;
        $user->save();

        // 🔑 توكين جديد
        $user->tokens()->delete();
        $token = $user->createToken('patient_api_token')->plainTextToken;

        return response()->json([
            'message'      => 'Patient registered successfully ✅',
            'user'         => $user,
            'token'        => $token,
            // ⚠️ المفتاح الخاص يرجع مرة وحدة فقط
            'private_key'  => $privateKeyPem
        ], 201);
    }

    /**
     * 🔐 Patient login
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

        if ($user->role !== 'PATIENT') {
            return response()->json(['message' => 'Access denied. Patients only.'], 403);
        }

        if ($user->status !== 'ACTIVE') {
            return response()->json(['message' => 'Your account is not active.'], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('patient_api_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful ✅',
            'user'    => $user,
            'token'   => $token
        ]);
    }

    /**
     * 🚪 Logout
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully ✅']);
    }

    /**
     * 👤 Profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }

    /**
     * 📋 Get all patients
     */
    public function index()
    {
        $patients = User::where('role', 'PATIENT')->get();

        return response()->json([
            'message'  => 'All patients fetched successfully ✅',
            'patients' => $patients
        ]);
    }
}
