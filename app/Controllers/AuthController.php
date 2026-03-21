<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Support\Validator;

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('/admin/dashboard');
        }

        render('auth/login', [
            'pageTitle' => 'Admin Login',
        ], 'admin');
    }

    public function login(): void
    {
        Csrf::ensure();

        $errors = Validator::login($_POST);

        if ($errors !== []) {
            remember_old_input($_POST);
            set_validation_errors($errors);
            redirect('/admin/login');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (!Auth::attempt(db(), $username, $password)) {
            remember_old_input(['username' => $username]);
            flash('error', 'Invalid username or password.');
            redirect('/admin/login');
        }

        clear_old_input();
        flash('success', 'Welcome back, ' . $username . '.');
        redirect('/admin/dashboard');
    }

    public function logout(): void
    {
        Csrf::ensure();
        $logoutReason = (string) ($_POST['logout_reason'] ?? '');
        Auth::logout();

        if ($logoutReason === 'timeout') {
            flash('warning', Auth::timeoutMessage());
        } else {
            flash('success', 'You have been logged out.');
        }

        redirect('/admin/login');
    }

    public function ping(): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            return;
        }

        Csrf::ensure();
        http_response_code(204);
    }
}
