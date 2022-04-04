<?php

namespace JeffersonSimaoGoncalves\Multitenancy\Middleware;

use Closure;
use JeffersonSimaoGoncalves\Multitenancy\Multitenancy;

class GuestTenantMiddleware
{
    /**
     * @var \JeffersonSimaoGoncalves\Multitenancy\Multitenancy
     */
    protected $multitenancy;

    /**
     * Create new TenantMiddleware instance.
     *
     * @param Illuminate\Contracts\Auth\Factory $auth
     * @param JeffersonSimaoGoncalves\Multitenancy\Multitenancy $multitenancy
     */
    public function __construct(Multitenancy $multitenancy)
    {
        $this->multitenancy = $multitenancy;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $tenant = $this->multitenancy->receiveTenantFromRequest();

        $this->multitenancy->setTenant($tenant)->applyTenantScopeToDeferredModels();

        return $next($request);
    }
}
