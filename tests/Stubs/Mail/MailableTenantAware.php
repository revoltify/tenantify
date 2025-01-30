<?php

namespace Revoltify\Tenantify\Tests\Stubs\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Revoltify\Tenantify\Jobs\TenantAware;

class MailableTenantAware extends Mailable implements ShouldQueue, TenantAware
{
    public function build(): Mailable
    {
        return $this->view('mailable');
    }
}
