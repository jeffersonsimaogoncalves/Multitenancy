<?php

namespace JeffersonSimaoGoncalves\Multitenancy\Middleware;

use Closure;
use JeffersonSimaoGoncalves\Multitenancy\Multitenancy;
use Illuminate\Contracts\Auth\Factory as Auth;
use JeffersonSimaoGoncalves\Multitenancy\Contracts\Tenant;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use JeffersonSimaoGoncalves\Multitenancy\Exceptions\UnauthorizedException;

class TenantMiddleware extends Middleware
{
    /**
     * @var \JeffersonSimaoGoncalves\Multitenancy\Multitenancy
     */
    protected $multitenancy;

    /**
     * Create new TenantMiddleware instance.
     *
     * @param Illuminate\Contracts\Auth\Factory       $auth
     * @param JeffersonSimaoGoncalves\Multitenancy\Multitenancy $multitenancy
     */
    public function __construct(Auth $auth, Multitenancy $multitenancy)
    {
        parent::__construct($auth);

        $this->multitenancy = $multitenancy;
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route(config('multitenancy.redirect_route'));
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string[]                 ...$guards
     *
     * @throws \JeffersonSimaoGoncalves\Multitenancy\Exceptions\UnauthorizedException|\Illuminate\Auth\AuthenticationException
     *
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        $tenant = $this->multitenancy->receiveTenantFromRequest();

        if (! $this->authorizedToAccessTenant($tenant)) {
            throw UnauthorizedException::forDomain($tenant->domain);
        }

        $this->multitenancy->setTenant($tenant)->applyTenantScopeToDeferredModels();

        $request->merge([Multitenancy::TENANT_SET_HEADER => true]);

        return $next($request);
    }

    /**
     * Check if user is authorized to access tenant's domain.
     *
     * @param \JeffersonSimaoGoncalves\Multitenancy\Contracts\Tenant $tenant
     *
     * @return bool
     */
    protected function authorizedToAccessTenant(Tenant $tenant)
    {
        return $tenant && $tenant->users->contains(auth()->user()->id);
    }
}
