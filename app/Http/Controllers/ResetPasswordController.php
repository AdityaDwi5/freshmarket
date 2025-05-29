<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon; // Import Carbon untuk pengelolaan waktu

class ResetPasswordController extends Controller
{
    // Fungsi untuk mereset password
    public function resetPassword(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Periksa apakah token valid dan belum kedaluwarsa
        $passwordReset = PasswordResetToken::where('token', $request->token)
                                            ->where('email', $request->email)
                                            ->first();

        if (!$passwordReset) {
            return response()->json(['message' => 'Invalid token or email.'], 400);
        }

        // Periksa apakah token sudah kedaluwarsa
        if (Carbon::now()->gt($passwordReset->expires_at)) {
            return response()->json(['message' => 'This token has expired.'], 400);
        }

        // Temukan user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Perbarui password pengguna
        $user->password = Hash::make($request->password);
        $user->save();

        // Hapus token setelah reset password berhasil
        $passwordReset->delete();

        return response()->json(['message' => 'Password has been reset successfully.']);
    }
}
