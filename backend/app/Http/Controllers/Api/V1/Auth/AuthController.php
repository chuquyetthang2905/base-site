<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Domains\Auth\Actions\GetAuthenticatedUserAction;
use App\Domains\Auth\Actions\LoginUserAction;
use App\Domains\Auth\Actions\LoginWithGoogleAction;
use App\Domains\Auth\Actions\LogoutUserAction;
use App\Domains\Auth\Actions\RefreshTokenAction;
use App\Domains\Auth\Actions\RegisterUserAction;
use App\Domains\Auth\DTOs\AuthSessionDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GoogleLoginRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\AuthSessionResource;
use App\Http\Resources\Auth\UserResource;
use App\Support\Http\RefreshTokenCookie;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Skinny controller: FormRequest -> DTO -> Action -> Resource. Nothing else.
 * Business errors are DomainExceptions rendered globally, so no try/catch here.
 */
final class AuthController extends Controller
{
    use ApiResponseTrait;

    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        return $this->created(
            new UserResource($action->execute($request->toDto())),
            'Registration successful',
        );
    }

    public function login(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        return $this->sessionResponse($action->execute($request->toDto()), 'Login successful');
    }

    public function google(GoogleLoginRequest $request, LoginWithGoogleAction $action): JsonResponse
    {
        return $this->sessionResponse($action->execute($request->toDto()), 'Login successful');
    }

    public function refresh(RefreshTokenRequest $request, RefreshTokenAction $action): JsonResponse
    {
        return $this->sessionResponse($action->execute($request->toDto()), 'Token refreshed successfully');
    }

    public function logout(LogoutRequest $request, LogoutUserAction $action): JsonResponse
    {
        $action->execute($request->toDto());

        return $this->success(null, 'Logged out successfully')
            ->withCookie(RefreshTokenCookie::forget());
    }

    public function me(Request $request, GetAuthenticatedUserAction $action): JsonResponse
    {
        return $this->success(
            new UserResource($action->execute((int) $request->user()->getAuthIdentifier())),
            'User retrieved successfully',
        );
    }

    private function sessionResponse(AuthSessionDTO $session, string $message): JsonResponse
    {
        return $this->success(new AuthSessionResource($session), $message)
            ->withCookie(RefreshTokenCookie::make($session->refreshToken));
    }
}
