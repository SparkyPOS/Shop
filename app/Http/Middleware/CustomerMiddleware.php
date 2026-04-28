<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomerMiddleware
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
        if (auth()->check()) {
            $roleType = auth()->user()->role->type ?? null;
            // Allow backend roles to use storefront cart/checkout without a separate customer login.
            if (!in_array($roleType, ['customer', 'seller', 'superadmin', 'admin', 'staff'], true)) {
                abort(404);
            }
            if ($roleType === 'customer' &&
                app('business_settings')->where('type', 'email_verification')->first()->status == 1 &&
                auth()->user()->is_verified == 0 &&
                auth()->user()->email != null) {
                return redirect('/user-email-verify');
            }
        }
        return $next($request);
    }
}
