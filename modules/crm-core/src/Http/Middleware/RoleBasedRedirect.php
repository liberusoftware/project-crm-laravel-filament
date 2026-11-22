<?php

namespace Liberu\CRM\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleBasedRedirect
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
