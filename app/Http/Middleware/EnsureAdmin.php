<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate the admin area. Even though the login redirect already sends admins to
 * their dashboard, every admin route is independently protected here so a
 * normal user cannot reach admin URLs by typing them in.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(Auth::check() && Auth::user()->isAdmin(), 403, 'Admins only.');

        return $next($request);
    }
}
