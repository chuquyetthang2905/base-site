<?php

declare(strict_types=1);

namespace App\Domains\Auth\Listeners;

use App\Domains\Auth\Events\UserRegistered;
use App\Domains\Auth\Mail\WelcomeMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

/** Runs on the Redis queue worker, never inside the HTTP request. */
final class SendWelcomeEmail implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(UserRegistered $event): void
    {
        Mail::to($event->user->email)->send(new WelcomeMail($event->user));
    }
}
