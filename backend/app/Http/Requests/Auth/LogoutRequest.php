<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Domains\Auth\DTOs\LogoutDTO;
use App\Support\Http\RefreshTokenCookie;
use Illuminate\Foundation\Http\FormRequest;

final class LogoutRequest extends FormRequest
{
    /** Route is already protected by auth:api. */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [];
    }

    public function toDto(): LogoutDTO
    {
        $token = $this->cookie(RefreshTokenCookie::NAME);

        return new LogoutDTO(rawRefreshToken: is_string($token) ? $token : null);
    }
}
