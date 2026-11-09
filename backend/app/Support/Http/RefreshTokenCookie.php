<?php

declare(strict_types=1);

namespace App\Support\Http;

use Symfony\Component\HttpFoundation\Cookie;

/**
 * HttpOnly refresh-token cookie: JS cannot read it (XSS), SameSite=Strict (CSRF),
 * path-scoped so it is only sent to the auth endpoints.
 */
final class RefreshTokenCookie
{
    public const NAME = 'refresh_token';

    private const PATH = '/api/v1/auth';

    public static function make(string $rawToken): Cookie
    {
        return cookie(
            name: self::NAME,
            value: $rawToken,
            minutes: 60 * 24 * (int) config('app.refresh_token_ttl_days', 30),
            path: self::PATH,
            secure: app()->isProduction(),
            httpOnly: true,
            sameSite: 'Strict',
        );
    }

    public static function forget(): Cookie
    {
        return cookie(
            name: self::NAME,
            value: '',
            minutes: -1,
            path: self::PATH,
            secure: app()->isProduction(),
            httpOnly: true,
            sameSite: 'Strict',
        );
    }
}
