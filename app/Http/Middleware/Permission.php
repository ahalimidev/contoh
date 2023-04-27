<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Permission
{

    public function handle(Request $request, Closure $next, $attributes = null)
    {
        return $next($request);
    }
}
