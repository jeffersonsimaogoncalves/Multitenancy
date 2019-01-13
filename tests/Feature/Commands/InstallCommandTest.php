<?php

namespace RomegaDigital\Multitenancy\Tests\Feature\Commands;

use Illuminate\Database\Schema\Blueprint;
use RomegaDigital\Multitenancy\Tests\Product;
use RomegaDigital\Multitenancy\Tests\TestCase;

class InstallCommandTest extends TestCase
{
    public $setupTestDatabase = false;


    /** @test */
    public function it_published_and_migrates_required_migrations_and_creates_admin_role_and_tenant()
    {
        $this->artisan('multitenancy:install')
            ->expectsOutput('Publishing required migrations...')
            ->expectsOutput('Migrations published!')
            ->expectsOutput('Adding `Super Administrator` Role...')
            ->expectsOutput('Role `Super Administrator` created')
            ->expectsOutput('Adding `admin` domain...')
            ->expectsOutput('Admin domain added successfully!');
    }
}
