<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Exception;

class AuthController extends Controller
{
    /**
     * Handle user login with JWT
     */
    public function login(Request $request)
    {
        try {
            Log::info('JWT Login attempt', [
                'email' => $request->input('email'),
            ]);

            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                Log::warning('JWT Login failed - invalid credentials', [
                    'email' => $request->input('email')
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Неверный email или пароль.',
                    'errors' => [
                        'email' => ['Неверный email или пароль.']
                    ]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user = Auth::user();

            Log::info('JWT Login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            $ttl = JWTAuth::factory()->getTTL();
            $cookie = $this->createAuthCookie($token, $ttl, $request);

            return response()->json([
                'success' => true,
                'user' => $user,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $ttl * 60,
                'message' => 'Вход выполнен успешно'
            ])->cookie($cookie);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Некорректные данные.',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (JWTException $e) {
            Log::error('JWT Token creation error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Не удалось создать токен.',
                'errors' => ['general' => ['Ошибка аутентификации. Попробуйте снова.']]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            Log::error('Login error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при входе.',
                'errors' => ['general' => ['Произошла непредвиденная ошибка.']]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Handle user registration with JWT
     */
    public function register(Request $request)
    {
        try {
            Log::info('JWT Registration attempt', [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
            ]);

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|regex:/^[\+]?[0-9\s\-\(\)]{10,20}$/',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
            ]);

            $token = JWTAuth::fromUser($user);

            Log::info('JWT Registration successful', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            $ttl = JWTAuth::factory()->getTTL();
            $cookie = $this->createAuthCookie($token, $ttl, $request);

            return response()->json([
                'success' => true,
                'user' => $user->fresh(),
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $ttl * 60,
                'message' => 'Регистрация успешна.'
            ], Response::HTTP_CREATED)->cookie($cookie);
        } catch (ValidationException $e) {
            Log::warning('Registration validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Некорректные данные.',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (JWTException $e) {
            Log::error('JWT Token creation error after registration', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Пользователь создан, но не удалось создать токен.',
                'errors' => ['general' => ['Попробуйте войти в систему.']]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            Log::error('Registration error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при регистрации.',
                'errors' => ['general' => ['Произошла непредвиденная ошибка.']]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        try {
            $user = Auth::user();
            Log::info('JWT Logout initiated', ['user_id' => $user?->id]);

            JWTAuth::invalidate(JWTAuth::getToken());

            $cookie = $this->createAuthCookie(null, -1, $request);

            return response()->json([
                'success' => true,
                'message' => 'Выход выполнен успешно'
            ])->cookie($cookie);
        } catch (JWTException $e) {
            Log::error('JWT Logout error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Не удалось выйти из системы.',
                'errors' => ['general' => ['Ошибка при выходе.']]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Refresh JWT token
     */
    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
                'message' => 'Токен обновлён'
            ]);
        } catch (JWTException $e) {
            Log::error('JWT Refresh error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Не удалось обновить токен.',
                'errors' => ['general' => ['Войдите в систему заново.']]
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Get authenticated user data
     */
    public function user()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не аутентифицирован.',
                    'errors' => ['auth' => ['Пользователь не аутентифицирован.']]
                ], Response::HTTP_UNAUTHORIZED);
            }

            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        } catch (Exception $e) {
            Log::error('Get user error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении данных пользователя.',
                'errors' => ['general' => ['Произошла ошибка.']]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email']);

            // Always return success for security
            return response()->json([
                'success' => true,
                'message' => 'Если email существует, на него будет отправлена ссылка для сброса пароля.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Некорректный email.',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $tokenRecord = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$tokenRecord || !Hash::check($request->token, $tokenRecord->token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Недействительный токен.',
                    'errors' => ['token' => ['Недействительный или истекший токен.']]
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не найден.',
                    'errors' => ['email' => ['Пользователь не найден.']]
                ], Response::HTTP_NOT_FOUND);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Пароль успешно изменён.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Некорректные данные.',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Create auth cookie with proper settings for localhost/production
     */
    private function createAuthCookie(?string $token, int $ttl, Request $request)
    {
        $isLocalhost = $this->isLocalhost($request);

        return cookie(
            'n_auth_token',
            $token,
            $ttl,
            '/',
            null,                      // Domain - null for localhost
            !$isLocalhost,             // Secure - false for localhost (HTTP), true for production (HTTPS)
            true,                      // HttpOnly
            false,                     // Raw
            $isLocalhost ? 'lax' : 'none'  // SameSite - lax for localhost, none for production
        );
    }

    /**
     * Check if request is from localhost
     */
    private function isLocalhost(Request $request): bool
    {
        $origin = $request->header('Origin') ?? '';
        return str_contains($origin, 'localhost') || str_contains($origin, '127.0.0.1');
    }
}
