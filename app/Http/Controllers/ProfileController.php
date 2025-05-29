<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Menampilkan profil pengguna yang sedang login.
     */
    public function show()
    {
        $user = Auth::user(); // Mendapatkan pengguna yang sedang login

        // Menggunakan JsonResource untuk respons terstruktur
        return response()->json([
            'message' => 'Profil pengguna berhasil diambil.',
            'data' => $user
        ], 200);
    }

    /**
     * Memperbarui profil pengguna yang sedang login.
     */
    public function update(Request $request)
    {
        $user = Auth::user(); // Mendapatkan pengguna yang sedang login

        // Validasi data yang diterima
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id, // Memastikan email unik kecuali untuk pengguna yang sedang login
            'password' => 'nullable|string|min:8', // Password opsional, jika ada perubahan
            'phone_number' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'role_id' => 'nullable|exists:roles,id', // Pastikan role_id valid jika diberikan
        ]);

        // Memperbarui profil pengguna menggunakan query builder
        User::where('id', $user->id) // Menemukan pengguna berdasarkan ID
            ->update($validatedData); // Memperbarui data pengguna

        // Menarik kembali data pengguna yang sudah diperbarui
        $user = User::find($user->id);

        // Respons sukses
        return response()->json([
            'message' => 'Profil pengguna berhasil diperbarui.',
            'data' => $user
        ], 200);
    }
}
