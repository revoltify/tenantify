<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Tests\Stubs\Models;

use Revoltify\Tenantify\Models\Concerns\BelongsToTenant;

class UserTest extends \Illuminate\Database\Eloquent\Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected $table = 'users';
}
