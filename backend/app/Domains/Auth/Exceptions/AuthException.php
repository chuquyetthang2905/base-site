<?php

declare(strict_types=1);

namespace App\Domains\Auth\Exceptions;

use App\Support\Http\RefreshTokenCookie;
use App\Support\Exceptions\DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthException extends DomainException
{
    private bool $clearsRefreshCookie = false;

    public static function invalidCredentials(): self
    {
        // Generic on purpose: never reveal whether the email or the password was wrong.
        return new self('Invalid credentials', 401);
    }

    public static function accountDeactivated(): self
    {
        return new self('Your account has been deactivated', 403);
    }

    public static function missingRefreshToken(): self
    {
        return new self('No refresh token provided', 401);
    }

    public static function invalidRefreshToken(): self
    {
        return (new self('Invalid refresh token', 401))->clearingCookie();
    }

    public static function expiredRefreshToken(): self
    {
        return (new self('Refresh token expired', 401))->clearingCookie();
    }

    public static function refreshTokenReused(): self
    {
        return (new self('Refresh token reuse detected. Please log in again.', 401))->clearingCookie();
    }

    public static function invalidGoogleToken(): self
    {
        return new self('Invalid Google token', 401);
    }

    public static function incompleteGoogleProfile(): self
    {
        return new self('Could not retrieve user info from Google', 401);
    }

    public function render(Request $request): JsonResponse
    {
        $response = parent::render($request);

        return $this->clearsRefreshCookie
            ? $response->withCookie(RefreshTokenCookie::forget())
            : $response;
    }

    private function clearingCookie(): self
    {
        $this->clearsRefreshCookie = true;

        return $this;
    }
}
