<?php

namespace App\Http\Controllers;

// use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\URL;
// use Illuminate\Support\Carbon;
// use App\Mail\VerifyEmail;
// use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:50',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => Role::where('name', 'customer')->first()->id,
            ]);

            // ===============================================================
                // TODO: ini nanti dipindah di providers
                // $verificationUrl = URL::temporarySignedRoute(
                //     'verification.verify',
                //     now()->addMinutes(10),
                //     ['id' => $user->id, 'hash' => sha1($user->email)]
                // );
                // Mail::to($user->email)->send(new VerifyEmail($user->name, $verificationUrl));

                // event(new Registered($user));
                // return response()->json(['data' => $user, 'message' => 'Silakan lakukan verifikasi melalui email yang telah kami kirimkan.'], 201);
            // ===============================================================

            $responUser = [
                'name' => $user->name,
                'email' => $user->email,
                'token' => JWTAuth::fromUser($user),
            ];
            // $token = JWTAuth::fromUser($user);

            return response()->json($responUser, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat memproses permintaan Anda.', 'details' => $e->getMessage()], 500);
        }
    }

// =========================================================================================
    // Controller untuk verifikasi email
    // public function verifyNotice() {
    //     return response()->json(['message' => 'Silakan lakukan verifikasi melalui email yang telah kami kirimkan.'], 401);
    // }

    // public function verifyEmail(EmailVerificationRequest $request) {
    //     $request->fulfill();
    //     return response()->json(['message' => 'verifikasi email berhasil']);
    // }

    // public function emailHandler(Request $request) {
    //     $request->user()->sendEmailVerificationNotification();
    //     return response()->json(['message' => 'link verifikasi telah dikirim']);
    // }
// =========================================================================================

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
        return response()->json(compact('token'));
    }

    public function user(Request $request)
    {
        return response()->json(auth('api')->user());
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
}
