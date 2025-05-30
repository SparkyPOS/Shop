<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookAuth
{
    private $privateKey;

    public function __construct()
    {
        $this->privateKey = env('WEBHOOK_AUTH_KEY');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Extract Auth Key from Headers
        $authKey = $request->header('X-Webhook-Auth');
        if (!$authKey) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $origin = $request->header('Origin');
        if (!$origin) {
            return response()->json(['error' => 'Missing Heaer'], 400);
        }

        // Parse domain from the Origin URL
        $domain = parse_url($origin, PHP_URL_HOST);
        if (!$domain) {
            return response()->json(['error' => 'Invalid Origin'], 400);
        }

        //Validate Authrntication Key
        $expectedPublicKey = hash_hmac('sha256', $domain, $this->privateKey);
        if (!hash_equals($expectedPublicKey, $authKey)) {
            return response()->json(['error' => 'Invalid authentication Key', 403]);
        }

        return $next($request);
    }
}
