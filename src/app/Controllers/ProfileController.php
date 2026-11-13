<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Support\ActivityLogger;
use App\Support\Auth;
use App\Support\Flash;
use App\Support\Value;
use App\Support\View;
use RuntimeException;

final class ProfileController
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function editPassword(): void
    {
        Auth::require();

        $this->renderForm([]);
    }

    public function updatePassword(): void
    {
        Auth::require();

        $id = Auth::id();

        if ($id === null) {
            redirect('/login');
        }

        $user = $this->users->findById($id);

        if (!$user instanceof User) {
            throw new RuntimeException('The signed-in user no longer exists.');
        }

        $current = Value::string($_POST, 'current_password', '', false);
        $new = Value::string($_POST, 'password', '', false);
        $confirmation = Value::string($_POST, 'password_confirmation', '', false);

        $errors = [];

        if ($current === '' || !password_verify($current, $user->passwordHash)) {
            $errors['current_password'] = 'Your current password is incorrect.';
        }

        if (mb_strlen($new) < 10) {
            $errors['password'] = 'The new password must be at least 10 characters long.';
        } elseif (preg_match('/[A-Za-z]/', $new) !== 1 || preg_match('/\d/', $new) !== 1) {
            $errors['password'] = 'The new password must contain at least one letter and one number.';
        } elseif ($new === $current) {
            $errors['password'] = 'The new password must be different from your current password.';
        }

        if ($confirmation !== $new) {
            $errors['password_confirmation'] = 'The password confirmation does not match.';
        }

        if ($errors !== []) {
            http_response_code(422);
            $this->renderForm($errors);

            return;
        }

        $this->users->updatePasswordHash($id, password_hash($new, PASSWORD_DEFAULT));

        ActivityLogger::log(
            'update_password',
            'auth',
            $id,
            sprintf('%s changed their password.', $user->fullName),
        );

        session_regenerate_id(true);

        Flash::success('Your password was updated successfully.');
        redirect('/profile/password');
    }

    /**
     * @param array<string, string> $errors
     */
    private function renderForm(array $errors): void
    {
        View::render('profile/password', [
            'pageTitle'    => 'Change password',
            'pageSubtitle' => 'Keep your staff account secure',
            'errors'       => $errors,
        ]);
    }
}
