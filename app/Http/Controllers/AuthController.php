<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Models\PasswordResetCode;
use App\Models\ActivityLog;
use App\Mail\PasswordResetCode as PasswordResetCodeMail;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['login', 'showLoginForm', 'showForgotPasswordForm', 'sendResetCode', 'showResetForm', 'resetPassword']]);
        $this->middleware('guest', ['only' => ['showLoginForm', 'login', 'showForgotPasswordForm', 'sendResetCode', 'showResetForm', 'resetPassword']]);
    }

    /**
     * Show login form
     */
    public function showLoginForm()
    {
        // Clear any existing flash messages that might interfere
        session()->forget(['errors', 'message']);
        
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        // Check if user exists first
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            return redirect()->back()
                ->withErrors(['email' => 'Aucun compte trouvé avec cette adresse email.'])
                ->withInput();
        }

        // Check if user is active
        if (!$user->is_active) {
            // Log failed login for inactive user
            try {
                ActivityLog::logAuth('failed_login_inactive', $user, [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'reason' => 'account_disabled'
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to log inactive user login attempt', ['error' => $e->getMessage()]);
            }
            
            return redirect()->back()
                ->withErrors(['email' => 'Votre compte a été désactivé. Contactez l\'administrateur.'])
                ->withInput();
        }

        // Attempt authentication with active user check
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'is_active' => true], $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Update last login info
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
            
            // Log successful login
            try {
                ActivityLog::logAuth('login', $user, [
                    'ip' => $request->ip(), 
                    'user_agent' => $request->userAgent(),
                    'remember' => $remember,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to log successful login', ['error' => $e->getMessage()]);
            }

            // Redirect based on user role
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            } else {
                return redirect()->intended(route('pharmacist.dashboard'));
            }
        } else {
            // Log failed login
            try {
                if ($user) {
                    ActivityLog::logAuth('failed_login', $user, [
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to log failed login attempt', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->back()
            ->withErrors(['email' => 'Ces identifiants ne correspondent pas à nos enregistrements.'])
            ->withInput();
    }

    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send reset code to user's email
     */
    public function sendResetCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $email = $request->email;
        
        // Rate limiting: max 3 attempts per hour per email
        $key = 'password-reset-' . $email;
        
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return redirect()->back()
                ->withErrors(['email' => "Trop de tentatives. Réessayez dans " . ceil($seconds/60) . " minutes."])
                ->withInput();
        }

        RateLimiter::hit($key, 3600); // 1 hour window

        $user = User::where('email', $email)->first();

        // Log password reset request
        Log::info('Password reset code generation attempt', [
            'email' => $email,
            'user_id' => $user->id,
            'ip' => $request->ip()
        ]);

        // Log reset request activity
        try {
            ActivityLog::logActivity(
                'reset',
                "Demande de réinitialisation de mot de passe pour: {$user->name}",
                $user,
                null,
                ['ip' => $request->ip(), 'email' => $email]
            );
        } catch (\Exception $e) {
            Log::warning('Failed to log password reset request', ['error' => $e->getMessage()]);
        }

        try {
            // Generate code
            $code = PasswordResetCode::generateCode($email);
            
            Log::info('Password reset code generated', [
                'email' => $email,
                'code' => $code
            ]);

            // Try to send email
            $mailSent = false;
            $errorMessage = '';
            
            try {
                ini_set('max_execution_time', 30);
                Mail::to($email)->send(new PasswordResetCodeMail($code, $user->name));
                $mailSent = true;
                
                Log::info('Password reset code email sent successfully', [
                    'email' => $email,
                    'user_id' => $user->id
                ]);
                
            } catch (\Exception $mailException) {
                $errorMessage = $mailException->getMessage();
                Log::error('Failed to send password reset code email', [
                    'email' => $email,
                    'error' => $errorMessage,
                ]);
            }
            
            ini_set('max_execution_time', 60);
            
            if ($mailSent) {
                return redirect()->route('password.reset.form', ['email' => $email])
                    ->with('success', 'Un code de vérification a été envoyé à votre adresse email.');
            } else {
                if (config('app.debug')) {
                    return redirect()->route('password.reset.form', ['email' => $email])
                        ->with('success', "Code de vérification généré: <strong>{$code}</strong> (Email non envoyé: {$errorMessage})");
                } else {
                    return redirect()->back()
                        ->withErrors(['email' => 'Erreur lors de l\'envoi de l\'email.'])
                        ->withInput();
                }
            }
                
        } catch (\Exception $e) {
            Log::error('Failed to generate password reset code', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->back()
                ->withErrors(['email' => 'Erreur lors de la génération du code.'])
                ->withInput();
        }
    }

    /**
     * Show reset password form
     */
    public function showResetForm(Request $request)
    {
        $email = $request->query('email');
        
        if (!$email) {
            return redirect()->route('password.forgot')
                ->withErrors(['email' => 'Email requis pour la réinitialisation.']);
        }

        return view('auth.reset-password', compact('email'));
    }

    /**
     * Reset password with verification code
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        // Check for too many attempts
        if (PasswordResetCode::hasTooManyAttempts($request->email)) {
            Log::warning('Password reset blocked due to too many attempts', [
                'email' => $request->email,
                'ip' => $request->ip()
            ]);
            
            return redirect()->back()
                ->withErrors(['code' => 'Trop de tentatives incorrectes. Demandez un nouveau code.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }

        // Verify code
        if (!PasswordResetCode::verifyCode($request->email, $request->code)) {
            Log::warning('Invalid password reset code attempted', [
                'email' => $request->email,
                'ip' => $request->ip()
            ]);
            
            return redirect()->back()
                ->withErrors(['code' => 'Code de vérification invalide ou expiré.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now()
        ]);

        // Log successful password reset
        try {
            ActivityLog::logActivity(
                'reset',
                "Mot de passe réinitialisé avec succès pour: {$user->name}",
                $user,
                null,
                ['ip' => $request->ip(), 'reset_time' => now()->toDateTimeString()]
            );
        } catch (\Exception $e) {
            Log::warning('Failed to log password reset success', ['error' => $e->getMessage()]);
        }

        Log::info('Password reset successful', [
            'user_id' => $user->id,
            'email' => $request->email,
            'ip' => $request->ip()
        ]);

        // Clean expired codes
        PasswordResetCode::cleanExpired();

        return redirect()->route('login')
            ->with('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
    }

    /**
     * Log the user out - FIXED VERSION
     */
    public function logout(Request $request)
    {
        // Log logout activity BEFORE logging out
        if (auth()->check()) {
            $user = auth()->user();
            
            // Calculate session duration
            $sessionDuration = 'unknown';
            if ($user->last_login_at) {
                $sessionDuration = $user->last_login_at->diffInMinutes(now()) . ' minutes';
            }
            
            try {
                ActivityLog::logAuth('logout', $user, [
                    'ip' => $request->ip(), 
                    'user_agent' => $request->userAgent(),
                    'logout_time' => now()->toDateTimeString(),
                    'session_duration' => $sessionDuration,
                    'session_id' => session()->getId()
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to log logout activity', ['error' => $e->getMessage()]);
            }
            
            Log::info('User logged out', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'session_duration' => $sessionDuration
            ]);
        }
        
        // Clear authentication
        Auth::logout();
        
        // Invalidate session and regenerate token
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Clear any remaining session data
        $request->session()->flush();
        
        // Redirect to login with success message
        return redirect()->route('login')->with('success', 'Vous avez été déconnecté avec succès.');
    }
}