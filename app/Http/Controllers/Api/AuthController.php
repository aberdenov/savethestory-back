<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'], 'password' => ['required', 'confirmed', PasswordRule::min(8) ]]);
        $user = User::create($data);

        return response()->json(['user' => $user, 'token' => $user->createToken('api')->plainTextToken], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) return response()
            ->json(['message' => 'Неверный email или пароль'], 422);

        return response()
            ->json(['user' => $user, 'token' => $user->createToken('api')->plainTextToken]);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $request->user() ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Вы вышли из аккаунта']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);
        $status = Password::sendResetLink($request->only('email'));
        
        return response()->json(['message' => __($status) ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate(['token' => ['required'], 'email' => ['required', 'email'], 'password' => ['required', 'confirmed', PasswordRule::min(8) ]]);
        
        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token') , function (User $user, string $password)
        {
            $user->forceFill(['password' => Hash::make($password) , 'remember_token' => Str::random(60) ])->save();
            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) return response()->json(['message' => __($status) ], 422);
        
        return response()->json(['message' => __($status) ]);
    }
}

