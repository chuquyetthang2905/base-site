<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Domains\Auth\DTOs\RefreshTokenDTO;
use App\Domains\Auth\Exceptions\AuthException;
use App\Support\Http\RefreshTokenCookie;
use Illuminate\Foundation\Http\FormRequest;

final class RefreshTokenRequest extends FormRequest
{
    /** The refresh token is only ever read from the HttpOnly cookie, never from the body. */
    public function authorize(): bool
    {
        return is_string($this->cookie(RefreshTokenCookie::NAME))
            && $this->cookie(RefreshTokenCookie::NAME) !== '';
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): never
    {
        throw AuthException::missingRefreshToken();
    }

    public function toDto(): RefreshTokenDTO
    {
        return new RefreshTokenDTO(rawToken: (string) $this->cookie(RefreshTokenCookie::NAME));
    }
}
