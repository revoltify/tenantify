<?php

namespace Revoltify\Tenantify\Tests\Stubs\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Revoltify\Tenantify\Jobs\NotTenantAware;

class MailableNotTenantAware extends Mailable implements NotTenantAware, ShouldQueue
{
    public function build(): Mailable
    {
        return $this->view('mailable');
    }
}
