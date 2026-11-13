<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\ActivityLogger;
use App\Support\Auth;
use App\Support\Flash;
use App\Support\Value;
use App\Support\View;

final class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('/');
        }

        View::render('auth/login', ['pageTitle' => 'Sign in'], 'auth-layout');
    }

    public function login(): void
    {
        $username = Value::string($_POST, 'username');
        $password = Value::string($_POST, 'password', '', false);

        if ($username === '' || $password === '') {
            Flash::error('Enter both your username and password.');
            redirect('/login');
        }

        if (Auth::lockedOut()) {
            Flash::error(sprintf(
                'Too many failed attempts. Please try again in %d seconds.',
                Auth::lockoutSecondsRemaining(),
            ));
            redirect('/login');
        }

        if (!Auth::attempt($username, $password)) {
            Flash::error('The username or password is incorrect.');
            redirect('/login');
        }

        $user = Auth::user();

        ActivityLogger::log(
            'login',
            'auth',
            Auth::id(),
            sprintf('%s signed in.', $user['full_name'] ?? $username),
        );

        Flash::success(sprintf('Welcome back, %s!', $user['full_name'] ?? $username));
        redirect('/');
    }

    public function logout(): void
    {
        $name = Auth::user()['full_name'] ?? 'User';

        ActivityLogger::log('logout', 'auth', Auth::id(), sprintf('%s signed out.', $name));
        Auth::logout();

        Flash::success('You have been signed out successfully.');
        redirect('/login');
    }
}
