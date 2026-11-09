<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Domains\Auth\DTOs\GoogleLoginDTO;
use Illuminate\Foundation\Http\FormRequest;

final class GoogleLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'credential' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'credential.required' => 'Missing credential',
        ];
    }

    public function toDto(): GoogleLoginDTO
    {
        return GoogleLoginDTO::fromValidated($this->validated());
    }
}
