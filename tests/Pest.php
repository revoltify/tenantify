<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Revoltify\Tenantify\Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');

function tempFile(string $fileName): string
{
    $tempDir = __DIR__.'/temp';

    throw_if(! is_dir($tempDir) && ! mkdir($tempDir, 0777, true) && ! is_dir($tempDir), RuntimeException::class, 'Failed to create temp directory: '.$tempDir);

    return $tempDir.DIRECTORY_SEPARATOR.$fileName;
}
