<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use App\Mail\SendEmail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Mail\ForgotPasswordEmail;
use App\Helpers\ResponseFormatter;
use Laravel\Fortify\Rules\Password;
use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone' => ['required', 'string',  'max:255'],
                'password' => ['required', 'string', new Password],
            ]);

            User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);
            $user = User::where('email', $request->email)->first();
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'acces_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'User Registered');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Authentication Failed', 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);
            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ], 'Authentication Failed', 500);
            }
            $user = User::where('email', $request->email)->first();
            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something when wrong',
                'error' => $error
            ], 'Authentication Failed', 500);
        }
    }

    public function fetch(Request $request)
    {
        // Ambil data user berserta relasi store dan user_addresses
        $user = $request->user()->load(['store', 'user_addresses']);

        // Tambahkan data alamat pengiriman terkait dalam struktur respons
        $userAddresses = $user->user_addresses->map(function ($address) {
            $addressData = $address->toArray();
            $addressData['id'] = $addressData['id'];
            unset($addressData[0]);
            return $addressData;
        });

        return ResponseFormatter::success([
            'user' => $user,
            // 'user_addresses' => $userAddresses->toArray(),
        ], 'Data profile user berhasil diambil');
    }
    public function updateProfile(Request $request)
    {
        $data = $request->all();
        $user = Auth::user();
        $user->update($data);
        return ResponseFormatter::success($user, 'Profile Updated');
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success($token, 'Token Revoked');
    }

    public function updateRole(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->update(['roles' => 'SELLER']);

            return ResponseFormatter::success($user, 'Role Updated');
        } else {
            return ResponseFormatter::error(null, 'Unauthorized', 401);
        }
    }

    public function updatePhoto(Request $request)
    {
        // Mendapatkan base URL dari environment
        $baseUrl = 'http://belanja.web.test/';

        //validasi dibutuhkan file bertipe image maksimal 4mb
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|max:4000'
        ]);

        //jika validatornya gagal
        if ($validator->fails()) {
            return ResponseFormatter::error(
                ['error' => $validator->errors()],
                'Update photo fails',
                401
            );
        }

        //jika validatornya berhasil -> cek file ada atau tidak
        if ($request->file('file')) {
            //proses upload
            $file = $request->file->store('assets/user', 'public');

            // Dapatkan URL lengkap berdasarkan file yang diunggah
            $url = $baseUrl . '/ecommerce/storage/app/public/' . $file;



            //simpan foto ke database (urlnya)
            $user = Auth::user();
            $user->profile_photo_path = $url;
            $user->update();

            // Menggunakan ResponseFormatter untuk menghasilkan respons
            return ResponseFormatter::success([
                "file" => $url
            ], 'File successfully uploaded');
        }
    }
    public function googleRedirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function googleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
            $existingUser = User::where('email', $user->email)->first();

            if ($existingUser) {
                $token = $existingUser->createToken('authToken')->plainTextToken;

                return ResponseFormatter::success([
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => $existingUser
                ], 'Authenticated');
            } else {
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => Hash::make(uniqid())
                ]);

                $token = $newUser->createToken('authToken')->plainTextToken;

                return ResponseFormatter::success([
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => $newUser
                ], 'Successful Login');
            }
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Authentication Failed', 500);
        }
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = \App\Models\User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified',
            ], 400);
        }

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Invalid verification token',
            ], 400);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return view('verification-email-success');

        // Redirect to home page after successful verification
        // return redirect()->route('home');
    }

    public function resendVerificationEmail(Request $request)
    {
        // Validasi input
        $request->validate([
            'user_id' => 'required',
        ]);

        $user = User::find($request->user_id);

        // Periksa status verifikasi email pengguna
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email sudah diverifikasi sebelumnya.',
            ], 400);
        }

        // Generate ulang verification_url dengan hash
        $expirationTime = Carbon::now()->addMinutes(15);
        $verificationToken = sha1($user->getEmailForVerification());
        $verificationUrl = 'http://belanja.web.test/public/api/email/verify/' . $user->getKey() . '/' . $verificationToken . '?expires=' . $expirationTime->timestamp;

        // Kirim ulang email verifikasi
        Mail::to($user->email)->send(new SendEmail($verificationUrl));

        // Berikan respon sukses dengan nama pengguna, ID, dan hash
        return response()->json([
            'message' => 'Email verifikasi telah dikirim ulang.',
            'user_id' => $user->getKey(),
            'verification_url' => $verificationUrl,
            'email_pengguna' => $user->email,
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Failed', 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return ResponseFormatter::error([
                'message' => 'Email tidak ditemukan',
            ], 'Email Not Found', 404);
        }

        $resetToken = Str::random(64);

        $user->update([
            'reset_password_token' => Hash::make($resetToken),
            'reset_password_created_at' => now(),
        ]);

        $resetPasswordUrl = 'http://belanja.web.test/public/edit-password?email=' . urlencode($user->email) . '&token=' . urlencode($resetToken);

        Mail::to($user->email)->send(new ForgotPasswordEmail($resetPasswordUrl));

        return ResponseFormatter::success([
            'message' => 'Email verifikasi untuk reset password telah dikirim.',
            'email' => $user->email,
            'token' => $resetToken,
        ], 'Forgot Password Email Sent', 200);
    }


    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => ['required', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Failed', 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return ResponseFormatter::error([
                'message' => 'Email tidak ditemukan',
            ], 'Email Not Found', 404);
        }

        // Periksa apakah token reset password sesuai
        if (!$user->reset_password_token || !hash_equals($user->reset_password_token, $request->token)) {
            return ResponseFormatter::error([
                'message' => 'Invalid reset password token',
            ], 'Invalid Token', 400);
        }

        if ($user->reset_password_created_at && now()->diffInMinutes($user->reset_password_created_at) > 60) {
            return ResponseFormatter::error([
                'message' => 'Reset password token has expired',
            ], 'Token Expired', 400);
        }

        // Periksa apakah sandi baru sama dengan sandi sebelumnya
        if (Hash::check($request->password, $user->password)) {
            return ResponseFormatter::error([
                'message' => 'The new password must be different from the previous password',
            ], 'Password Same', 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'reset_password_token' => null,
            'reset_password_created_at' => null,
        ]);

        return redirect()->route('reset-password-success')->with('success', 'Password has been successfully reset.');
    }


    public function showEditPasswordForm(Request $request)
    {
        $email = $request->query('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Jika email tidak ditemukan, tangani dengan memberikan respons kesalahan atau mengarahkan ke halaman lain.
            // Contoh respons kesalahan:
            return response()->json(['message' => 'Email tidak ditemukan'], 404);
        }

        $token = $user->reset_password_token;

        return view('edit-password.edit-password', [
            'email' => $email,
            'token' => $token,
        ]);
    }
}
