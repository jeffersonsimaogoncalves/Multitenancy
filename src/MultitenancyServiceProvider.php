<?php

namespace JeffersonSimaoGoncalves\Multitenancy;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use JeffersonSimaoGoncalves\Multitenancy\Commands\InstallCommand;
use JeffersonSimaoGoncalves\Multitenancy\Commands\MigrationMakeCommand;
use JeffersonSimaoGoncalves\Multitenancy\Commands\AssignAdminPrivileges;
use JeffersonSimaoGoncalves\Multitenancy\Contracts\Tenant as TenantContract;

class MultitenancyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the package services.
     *
     * @param Illuminate\Filesystem\Filesystem $filesystem
     */
    public function boot(Filesystem $filesystem)
    {
        $this->loadMigrationsFrom(realpath(__DIR__ . '/../migrations'));

        if ($this->app->runningInConsole()) {
            $this->registerPublishing($filesystem);
        }

        $this->registerCommands();
        $this->registerModelBindings();

        Gate::before(function ($user, $ability) {
            if ($user->hasRole(config('multitenancy.roles.super_admin'))
                && 'admin' === app('multitenancy')->getCurrentSubDomain()) {
                return true;
            }
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/multitenancy.php',
            'multitenancy'
        );

        $this->app->singleton(Multitenancy::class, function () {
            return new Multitenancy();
        });

        $this->app->alias(Multitenancy::class, 'multitenancy');
    }

    /**
     * Register the package's publishable resources.
     *
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     */
    protected function registerPublishing(Filesystem $filesystem)
    {
        $this->publishes([
            __DIR__ . '/../migrations/create_'.config('multitenancy.table_names.tenants').'_table.php.stub' => $this->getMigrationFileName($filesystem),
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/../config/multitenancy.php' => config_path('multitenancy.php'),
        ], 'config');
    }

    /**
     * Registers all commands within the package.
     */
    protected function registerCommands()
    {
        $this->commands([
            InstallCommand::class,
            MigrationMakeCommand::class,
            AssignAdminPrivileges::class,
        ]);
    }

    /**
     * Register model bindings.
     */
    protected function registerModelBindings()
    {
        $this->app->bind(TenantContract::class, $this->app->config['multitenancy.tenant_model']);
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     *
     * @return string
     */
    protected function getMigrationFileName(Filesystem $filesystem): string
    {
        $timestamp = date('Y_m_d_His');

        return Collection::make($this->app->databasePath() . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path . '*_create_tenants_table.php');
            })->push($this->app->databasePath() . "/migrations/{$timestamp}_create_tenants_table.php")
            ->first();
    }
}
