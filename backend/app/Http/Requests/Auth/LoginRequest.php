<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Domains\Auth\DTOs\LoginDTO;
use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            // Presence only: a wrong password is a business rule handled generically in the Action.
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'password.required' => 'Password is required.',
        ];
    }

    public function toDto(): LoginDTO
    {
        return LoginDTO::fromValidated($this->validated());
    }
}
