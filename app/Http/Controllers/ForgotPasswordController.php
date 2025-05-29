<?php
namespace App\Http\Controllers;

use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail; // Pastikan Anda telah membuat Mailable
use Carbon\Carbon; // Pastikan Anda sudah mengimpor Carbon

class ForgotPasswordController extends Controller
{
    // Fungsi untuk mengirimkan link reset password ke email
    public function sendResetLink(Request $request)
{
    // Validasi email
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    // Cari pengguna berdasarkan email
    $user = User::where('email', $request->email)->first();

    // Buat token acak untuk reset password
    $token = Str::random(60);

    // Hitung waktu kedaluwarsa (30 menit dari sekarang)
    $expiresAt = now()->addMinutes(30);

    // Simpan token ke dalam database
    PasswordResetToken::create([
        'email' => $request->email,
        'token' => $token,
        'expires_at' => $expiresAt,  // Simpan waktu kedaluwarsa
        'created_at' => now(),
    ]);

    // Kirim email berisi token reset password
    Mail::to($request->email)->send(new PasswordResetMail($token));

    // Kirim response dengan waktu kedaluwarsa
    return response()->json([
        'message' => 'Password reset token has been sent to your email.',
        'expires_at' => $expiresAt,  // Kirim waktu kedaluwarsa ke frontend
    ]);
}
}
