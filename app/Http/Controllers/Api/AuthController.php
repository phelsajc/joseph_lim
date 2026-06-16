<?php
/**
 * File AuthController.php
 *
 * @author Tuan Duong <bacduong@gmail.com>
 * @package Laravue
 * @version 1.0
 */
namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Laravue\JsonResponse;
use App\Laravue\Models\User;
use App\Mail\LoginOtpMail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/**
 * Class AuthController
 *
 * @package App\Http\Controllers\Api
 */
class AuthController extends BaseController
{
    /**
     * Max OTP emails per username+IP per rate window.
     */
    private const OTP_SEND_MAX = 5;

    /**
     * Rate window for OTP sends (minutes).
     */
    private const OTP_SEND_WINDOW_MINUTES = 15;

    /**
     * Login: first POST with username + password sends OTP to the user's email.
     * Second POST with username + password + otp completes login.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        // #region agent log
        $debugLog = function (string $message, array $data = [], string $hypothesisId = 'A') {
            file_put_contents(base_path('debug-e8512d.log'), json_encode([
                'sessionId' => 'e8512d',
                'timestamp' => (int) round(microtime(true) * 1000),
                'location' => 'AuthController.php:login',
                'message' => $message,
                'data' => $data,
                'hypothesisId' => $hypothesisId,
                'runId' => 'pre-fix',
            ]) . "\n", FILE_APPEND);
        };
        // #endregion
        if (!Auth::validate($credentials)) {
            // #region agent log
            $debugLog('login_validate_failed', ['username' => $credentials['username'] ?? null], 'A');
            // #endregion
            return response()->json(new JsonResponse([], 'login_error'), Response::HTTP_UNAUTHORIZED);
        }

        if (Auth::check()) {
            /** @var User|null $currentUser */
            $currentUser = Auth::user();
            if ($currentUser && $currentUser->username === $credentials['username']) {
                // #region agent log
                $debugLog('login_already_authenticated', ['userId' => $currentUser->id], 'C');
                // #endregion
                return response()->json(new JsonResponse(new UserResource($currentUser)), Response::HTTP_OK);
            }
        }

        /** @var User|null $user */
        $user = User::where('username', $credentials['username'])->first();
        if (!$user) {
            return response()->json(new JsonResponse([], 'login_error'), Response::HTTP_UNAUTHORIZED);
        }

        if ($user->login_otp_enabled && empty($user->email)) {
            return response()->json(new JsonResponse([], 'login_error_no_email'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$user->login_otp_enabled) {
            if ($request->filled('otp')) {
                return response()->json(new JsonResponse([], 'otp_not_required'), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            Auth::login($user);
            // #region agent log
            $debugLog('login_session_created', ['userId' => $user->id, 'authCheck' => Auth::check(), 'branch' => 'no_otp'], 'C');
            // #endregion

            return response()->json(new JsonResponse(new UserResource($user)), Response::HTTP_OK);
        }

        $ttlMinutes = max(1, (int) config('services.login_otp.ttl_minutes', 10));

        if ($request->filled('otp')) {
            $otp = preg_replace('/\s+/', '', (string) $request->input('otp'));
            if (!preg_match('/^\d{6}$/', $otp)) {
                return response()->json(new JsonResponse([], 'otp_invalid'), Response::HTTP_UNAUTHORIZED);
            }

            $cacheKey = 'login_otp:' . $user->id;
            $storedHash = Cache::get($cacheKey);
            if (!$storedHash || !hash_equals($storedHash, hash('sha256', $otp))) {
                return response()->json(new JsonResponse([], 'otp_invalid'), Response::HTTP_UNAUTHORIZED);
            }

            Cache::forget($cacheKey);
            Auth::login($user);
            // #region agent log
            $debugLog('login_session_created', ['userId' => $user->id, 'authCheck' => Auth::check(), 'branch' => 'otp_verified'], 'C');
            // #endregion

            return response()->json(new JsonResponse(new UserResource($user)), Response::HTTP_OK);
        }

        $rateKey = 'otp-send:' . sha1($credentials['username'] . '|' . $request->ip());
        $sendCount = (int) Cache::get($rateKey, 0);
        if ($sendCount >= self::OTP_SEND_MAX) {
            return response()->json(new JsonResponse([], 'otp_rate_limit'), Response::HTTP_TOO_MANY_REQUESTS);
        }
        Cache::put($rateKey, $sendCount + 1, now()->addMinutes(self::OTP_SEND_WINDOW_MINUTES));

        $otpCode = (string) random_int(100000, 999999);
        Cache::put('login_otp:' . $user->id, hash('sha256', $otpCode), now()->addMinutes($ttlMinutes));

        try {
            Mail::to($user->email)->send(new LoginOtpMail($otpCode, $user->name));
        } catch (\Throwable $e) {
            report($e);
            Cache::forget('login_otp:' . $user->id);

            return response()->json(new JsonResponse([], 'otp_email_failed'), Response::HTTP_SERVICE_UNAVAILABLE);
        }

        // #region agent log
        $debugLog('login_otp_sent_no_session', [
            'userId' => $user->id,
            'loginOtpEnabled' => (bool) $user->login_otp_enabled,
            'hasOtpInRequest' => $request->filled('otp'),
            'authCheck' => Auth::check(),
        ], 'A');
        // #endregion

        return response()->json(
            new JsonResponse([
                'otp_required' => true,
                'expires_in_minutes' => $ttlMinutes,
                'email' => $user->email,
            ]),
            Response::HTTP_OK
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        return response()->json((new JsonResponse())->success([]), Response::HTTP_OK);
    }

    /**
     * Update the authenticated user's password (current password required).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var User $user */
        $user = $request->user();
        if (!$user || !Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'errors' => ['current_password' => ['The current password is incorrect.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        $response = new JsonResponse();
        $response->success([]);

        return response()->json($response, Response::HTTP_OK);
    }

}
