<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Tests\Stubs\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Revoltify\Tenantify\Jobs\NotTenantAware;

final class NotificationNotTenantAware extends Notification implements NotTenantAware, ShouldQueue
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Message')
            ->greeting('Hello!')
            ->line('Say goodbye!');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
