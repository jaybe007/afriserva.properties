<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Database;
use App\Models\User;

class AuthController extends Controller
{
    public function loginForm(Request $request, Response $response): void
    {
        if (Auth::check()) {
            $this->redirect($response, url('dashboard'));
            return;
        }

        $html = $this->render('auth/login', [
            'title' => 'Sign In to Your Real Estate Portal | Afriserva Real Estate',
            'redirect' => $request->query('redirect', '')
        ]);

        $response->html($html);
    }

    public function login(Request $request, Response $response): void
    {
        $email = $request->input('email', '');
        $password = $request->input('password', '');
        $redirect = $request->input('redirect', '');
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $errors = $this->validate($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!empty($errors)) {
            $html = $this->render('auth/login', [
                'title' => 'Sign In | Afriserva Real Estate',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect
            ]);
            $response->html($html, 422);
            return;
        }

        $authResult = Auth::authenticate($email, $password, $clientIp);

        if (!$authResult['success']) {
            $status = str_contains($authResult['error'] ?? '', 'locked') ? 429 : 401;
            $html = $this->render('auth/login', [
                'title' => 'Sign In | Afriserva Real Estate',
                'errors' => ['auth' => $authResult['error']],
                'email' => $email,
                'redirect' => $redirect
            ]);
            $response->html($html, $status);
            return;
        }

        // Check if user requires Two-Factor Authentication
        if (!empty($authResult['requires_2fa'])) {
            $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            Database::execute(
                "UPDATE users SET two_factor_code = ?, two_factor_code_expires_at = (NOW() + INTERVAL 10 MINUTE) WHERE id = ?",
                [$otp, $authResult['user']['id']]
            );
            \App\Core\Session::set('2fa_pending_user_id', (int)$authResult['user']['id']);
            \App\Core\Session::set('2fa_demo_code', $otp);
            $this->redirect($response, url('login/2fa'));
            return;
        }

        flash('success', 'Welcome back to Afriserva Real Estate.');
        $destination = !empty($redirect) && str_starts_with($redirect, '/') ? url($redirect) : url('dashboard');
        $this->redirect($response, $destination);
    }

    public function registerForm(Request $request, Response $response): void
    {
        if (Auth::check()) {
            $this->redirect($response, url('dashboard'));
            return;
        }

        $html = $this->render('auth/register', [
            'title' => 'Create Your Account | Afriserva Real Estate',
            'role' => $request->query('role', 'client')
        ]);

        $response->html($html);
    }

    public function register(Request $request, Response $response): void
    {
        $input = $request->all();
        $errors = $this->validate($input, [
            'full_name' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'phone' => 'required',
            'terms_consent' => 'required'
        ]);

        // Check if email already registered
        if (empty($errors['email']) && User::findByEmail($input['email'])) {
            $errors['email'] = 'An account with this email address already exists.';
        }

        if (!empty($errors)) {
            $html = $this->render('auth/register', [
                'title' => 'Create Account | Afriserva Real Estate',
                'errors' => $errors,
                'input' => $input,
                'role' => $input['role'] ?? 'client'
            ]);
            $response->html($html, 422);
            return;
        }

        $allowedRoles = ['client', 'tenant', 'landlord', 'seller', 'agent', 'agency_admin', 'developer', 'property_manager'];
        $role = in_array($input['role'] ?? '', $allowedRoles, true) ? $input['role'] : 'client';

        $userId = User::create($input, $role);
        $user = User::findById($userId);

        Auth::login($user);
        flash('success', 'Your account has been created successfully! Welcome to Afriserva Real Estate.');
        $this->redirect($response, url('dashboard'));
    }

    public function forgotPasswordForm(Request $request, Response $response): void
    {
        $html = $this->render('auth/forgot_password', [
            'title' => 'Forgot Password | Afriserva Real Estate',
            'email' => $request->query('email', '')
        ]);

        $response->html($html);
    }

    public function sendResetLink(Request $request, Response $response): void
    {
        $email = trim(strtolower($request->input('email', '')));
        $token = User::createPasswordReset($email);

        if (!$token) {
            $html = $this->render('auth/forgot_password', [
                'title' => 'Forgot Password | Afriserva Real Estate',
                'errors' => ['email' => 'No active account found matching that email address.'],
                'email' => $email
            ]);
            $response->html($html, 404);
            return;
        }

        $resetUrl = url('reset-password?token=' . $token);

        $html = $this->render('auth/forgot_password', [
            'title' => 'Password Reset Link Generated | Afriserva Real Estate',
            'resetLink' => $resetUrl,
            'email' => $email
        ]);

        $response->html($html);
    }

    public function resetPasswordForm(Request $request, Response $response): void
    {
        $token = $request->query('token', '');
        $reset = User::validatePasswordReset($token);

        if (!$reset) {
            flash('danger', 'Password reset authorization link is invalid or has expired.');
            $this->redirect($response, url('forgot-password'));
            return;
        }

        $html = $this->render('auth/reset_password', [
            'title' => 'Set New Password | Afriserva Real Estate',
            'token' => $token
        ]);

        $response->html($html);
    }

    public function resetPassword(Request $request, Response $response): void
    {
        $token = $request->input('token', '');
        $password = $request->input('password', '');
        $passwordConfirmation = $request->input('password_confirmation', '');

        $errors = [];
        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }
        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            $html = $this->render('auth/reset_password', [
                'title' => 'Set New Password | Afriserva Real Estate',
                'token' => $token,
                'errors' => $errors
            ]);
            $response->html($html, 422);
            return;
        }

        $success = User::resetPasswordWithToken($token, $password);

        if (!$success) {
            flash('danger', 'Failed to reset password. Authorization token expired.');
            $this->redirect($response, url('forgot-password'));
            return;
        }

        flash('success', 'Your password has been successfully updated with Argon2id encryption. Please sign in.');
        $this->redirect($response, url('login'));
    }

    public function verifyEmail(Request $request, Response $response): void
    {
        $token = $request->query('token', '');
        if (!empty($token) && User::verifyEmailByToken($token)) {
            flash('success', 'Your email address has been verified successfully!');
        } else {
            flash('warning', 'Email verification token is invalid or already consumed.');
        }

        $this->redirect($response, url('dashboard/profile'));
    }

    public function verifyEmailInstant(Request $request, Response $response): void
    {
        $userId = Auth::id();
        if (!$userId) {
            $this->redirect($response, url('login'));
            return;
        }

        User::verifyUser($userId);
        flash('success', 'Your email address has been verified successfully!');
        $this->redirect($response, url('dashboard/profile'));
    }

    public function revokeSession(Request $request, Response $response, array $params): void
    {
        $sessionId = $params['id'] ?? '';
        $userId = Auth::id();

        if ($userId && !empty($sessionId)) {
            Auth::revokeSession($userId, $sessionId);
            flash('success', 'Active device session terminated.');
        }

        $this->redirect($response, url('dashboard/profile'));
    }

    public function revokeOtherSessions(Request $request, Response $response): void
    {
        $userId = Auth::id();
        $currentSessionId = session_id();

        if ($userId) {
            Auth::revokeOtherSessions($userId, $currentSessionId);
            flash('success', 'All other active devices and browser sessions have been logged out.');
        }

        $this->redirect($response, url('dashboard/profile'));
    }

    public function requestAccountDeletion(Request $request, Response $response): void
    {
        $userId = Auth::id();
        $reason = trim($request->input('reason', 'User requested account closure.'));

        if ($userId) {
            User::requestDeletion($userId, $reason);
            flash('info', 'Your account deletion request has been registered and is pending statutory compliance review.');
        }

        $this->redirect($response, url('dashboard/profile'));
    }

    public function twoFactorForm(Request $request, Response $response): void
    {
        $userId = \App\Core\Session::get('2fa_pending_user_id');
        if (!$userId) {
            $this->redirect($response, url('login'));
            return;
        }

        $html = $this->render('auth/two_factor', [
            'title' => 'Two-Factor Authentication Challenge | Afriserva Real Estate',
            'demoCode' => \App\Core\Session::get('2fa_demo_code')
        ]);

        $response->html($html);
    }

    public function verifyTwoFactor(Request $request, Response $response): void
    {
        $userId = (int)\App\Core\Session::get('2fa_pending_user_id');
        $code = trim($request->input('two_factor_code', ''));

        if (!$userId) {
            $this->redirect($response, url('login'));
            return;
        }

        $user = Database::fetch(
            "SELECT * FROM users WHERE id = ? AND two_factor_code = ? AND two_factor_code_expires_at >= NOW() LIMIT 1",
            [$userId, $code]
        );

        if (!$user) {
            $html = $this->render('auth/two_factor', [
                'title' => 'Two-Factor Authentication Challenge | Afriserva Real Estate',
                'errors' => ['2fa' => 'The security code entered is invalid or has expired.'],
                'demoCode' => \App\Core\Session::get('2fa_demo_code')
            ]);
            $response->html($html, 401);
            return;
        }

        // Clear consumed 2FA code
        Database::execute("UPDATE users SET two_factor_code = NULL, two_factor_code_expires_at = NULL WHERE id = ?", [$userId]);
        \App\Core\Session::remove('2fa_pending_user_id');
        \App\Core\Session::remove('2fa_demo_code');

        $fullUser = User::findById($userId);
        Auth::login($fullUser);

        \App\Helpers\AuditLogger::log('user_2fa_success', 'user', $userId);

        flash('success', 'Two-Factor Authentication verified successfully. Welcome back.');
        $this->redirect($response, url('dashboard'));
    }

    public function verifyPhone(Request $request, Response $response): void
    {
        $userId = Auth::id();
        if ($userId) {
            Database::execute("UPDATE users SET phone_verified_at = NOW() WHERE id = ?", [$userId]);
            \App\Helpers\AuditLogger::log('phone_verified', 'user', $userId);
            flash('success', 'Your contact phone number has been verified via SMS gateway.');
        }

        $this->redirect($response, url('dashboard/profile'));
    }

    public function logout(Request $request, Response $response): void
    {
        Auth::logout();
        flash('info', 'You have been safely signed out.');
        $this->redirect($response, url('login'));
    }
}
