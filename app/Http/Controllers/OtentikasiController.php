<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;


class OtentikasiController extends Controller
{
    public function login(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        // Cari pengguna berdasarkan email
        $user = User::where('email', $validatedData['email'])->first();

        // Jika pengguna tidak ditemukan atau password salah
        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 401); // Unauthorized
        }

        // Buat token untuk pengguna
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => [
                    'id' => $user->role->id,
                    'name' => $user->role->name,
                ],
            ],
            'token' => $token,
        ]);
    }

    public function register(Request $request)
    {
        // Validasi data input
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'nullable|uuid|exists:roles,id', // Role opsional
            'address' => 'nullable|string|max:255',
            'email_verified_at' => 'nullable|date',
        ]);

        // Tetapkan default role jika `role_id` tidak diisi
        $defaultRoleId = 'f88bc757-b55d-11ef-99dc-201a0636317b'; // Ganti dengan UUID role 'customer'

        // Membuat user baru
        $user = User::create([
            'id' => Str::uuid(),
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role_id' => $validatedData['role_id'] ?? $defaultRoleId, // Gunakan default role jika kosong
            'address' => $validatedData['address'] ?? null,
            'email_verified_at' => $validatedData['email_verified_at'] ?? null,
        ]);

        // Kirim email verifikasi jika diperlukan (tambahkan verifikasi email jika dibutuhkan)

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user->only(['id', 'name', 'email', 'role_id', 'address', 'email_verified_at']),
        ], 201);
    }

    public function logout(Request $request)
    {
        try {
            // Ambil user yang sedang login
            $user = Auth::user(); // Mendapatkan user yang sedang aktif berdasarkan token yang digunakan

            // Jika user tidak ditemukan
            if (!$user) {
                return Response::json([
                    'message' => 'User not found or invalid token'
                ], 401); // Kode error 401 Unauthorized
            }

            // Hapus token yang digunakan untuk otentikasi
            $user->tokens->each(function ($token) {
                $token->delete(); // Menghapus token yang terkait dengan user
            });

            return Response::json([
                'message' => 'Logged out successfully'
            ], 200); // Kode sukses 200
        } catch (\Exception $e) {
            // Jika ada error
            return Response::json([
                'message' => 'An error occurred during logout',
                'error' => $e->getMessage()
            ], 500); // Kode error 500 Internal Server Error
        }
    }
}
