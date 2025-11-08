<?php

namespace Modules\AdminInstruktur\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstrukturMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated with admin_instruktur guard
        if (!Auth::guard('admin_instruktur')->check()) {
            return redirect()->route('admin.login');
        }

        // Get the authenticated user
        $user = Auth::guard('admin_instruktur')->user();

        // Check if user has instructor role
        if ($user->role !== 'instruktur') {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
