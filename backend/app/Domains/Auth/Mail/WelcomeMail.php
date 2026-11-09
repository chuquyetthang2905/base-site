<?php

declare(strict_types=1);

namespace App\Domains\Auth\Mail;

use App\Domains\Auth\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class WelcomeMail extends Mailable
{
    public function __construct(
        public readonly User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome to '.config('app.name'));
    }

    public function content(): Content
    {
        $name = e($this->user->name);
        $app = e((string) config('app.name'));

        return new Content(
            htmlString: "<p>Hi {$name},</p><p>Your account on {$app} has been created successfully.</p>",
        );
    }
}
