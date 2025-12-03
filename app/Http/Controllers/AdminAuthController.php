<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    /**
     * 🧑‍💻 Register Admin
     * ⚠️ عادة يتم التسجيل من طرف النظام وليس عبر API عام
     */
    public function register(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|regex:/@gmail\.com$/|unique:users,email',
            'phone'    => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'email'         => $request->email,
            'phone'         => $request->phone,
            'password_hash' => Hash::make($request->password),
            'role'          => 'ADMIN',
            'status'        => 'ACTIVE',
        ]);

        // إنشاء مفتاح تشفير عام (اختياري للأدمن)
        $keyPair = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        openssl_pkey_export($keyPair, $privateKeyPem);
        $keyDetails = openssl_pkey_get_details($keyPair);
        $publicKeyPem = $keyDetails['key'];

        $user->public_key = $publicKeyPem;
        $user->save();

        $user->tokens()->delete();
        $token = $user->createToken('admin_api_token')->plainTextToken;

        return response()->json([
            'message'      => 'Admin registered successfully ✅',
            'user'         => $user,
            'token'        => $token,
            'private_key'  => $privateKeyPem
        ], 201);
    }

    /**
     * 🔐 Admin login
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

        if ($user->role !== 'ADMIN') {
            return response()->json(['message' => 'Access denied. Admin only.'], 403);
        }

        if ($user->status !== 'ACTIVE') {
            return response()->json(['message' => 'Your account is not active.'], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('admin_api_token')->plainTextToken;

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
     * 📋 Get all Admins (اختياري)
     */
    public function index()
    {
        $admins = User::where('role', 'ADMIN')->get();

        return response()->json([
            'message' => 'All admins fetched successfully ✅',
            'admins'  => $admins
        ]);
    }
}
