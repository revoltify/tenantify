<?php

declare(strict_types=1);

namespace Revoltify\Tenantify\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Revoltify\Tenantify\Models\Concerns\BelongsToTenant;

class UserTest extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected $table = 'users';
}
