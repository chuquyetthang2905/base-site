<?php

declare(strict_types=1);

namespace App\Http\Resources\Auth;

use App\Domains\Auth\DTOs\AuthSessionDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property AuthSessionDTO $resource
 *
 * The refresh token is deliberately absent: it travels only in the HttpOnly cookie.
 */
final class AuthSessionResource extends JsonResource
{
    public function __construct(AuthSessionDTO $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this->resource->accessToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($this->resource->user),
        ];
    }
}
