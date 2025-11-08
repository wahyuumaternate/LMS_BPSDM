<?php

namespace Modules\AdminInstruktur\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if ($guard == 'admin_instruktur' && Auth::guard($guard)->check()) {
                // If admin_instruktur is logged in, redirect to admin dashboard
                return redirect()->route('admin.dashboard');
            }

            if (Auth::guard($guard)->check()) {
                // For other guards, use the default home
                return redirect()->route('home');
            }
        }

        return $next($request);
    }
}
