<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Revoltify\Tenantify\Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');

function tempFile(string $fileName): string
{
    return __DIR__."/temp/{$fileName}";
}
