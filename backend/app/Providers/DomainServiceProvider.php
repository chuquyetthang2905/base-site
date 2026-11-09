<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Auth\Events\RefreshTokenReuseDetected;
use App\Domains\Auth\Events\UserRegistered;
use App\Domains\Auth\Listeners\LogRefreshTokenReuse;
use App\Domains\Auth\Listeners\SendWelcomeEmail;
use App\Domains\Auth\Repositories\RefreshTokenRepository;
use App\Domains\Auth\Repositories\RefreshTokenRepositoryInterface;
use App\Domains\Auth\Repositories\UserRepository;
use App\Domains\Auth\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Single registry for every module under app/Domains:
 * Repository interface bindings and Event => Listener maps.
 * (Laravel only auto-discovers app/Listeners, so Domain listeners are mapped here.)
 * Add one section per new module.
 */
final class DomainServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        // Auth
        UserRepositoryInterface::class => UserRepository::class,
        RefreshTokenRepositoryInterface::class => RefreshTokenRepository::class,
    ];

    public function boot(): void
    {
        // Auth (all listeners implement ShouldQueue -> Redis queue worker)
        Event::listen(UserRegistered::class, SendWelcomeEmail::class);
        Event::listen(RefreshTokenReuseDetected::class, LogRefreshTokenReuse::class);
    }
}
