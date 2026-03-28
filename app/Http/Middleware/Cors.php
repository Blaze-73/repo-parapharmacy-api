<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        // Allow the app's own origin in production, localhost in dev
        $origin = $request->header('Origin');

        $allowed = [
            'http://localhost:5173',
            'http://localhost:3000',
            env('APP_URL', ''),
        ];

        $allowOrigin = in_array($origin, array_filter($allowed))
            ? $origin
            : env('APP_URL', 'http://localhost:5173');

        if ($request->getMethod() === 'OPTIONS') {
            return response('', 200)
                ->header('Access-Control-Allow-Origin',  $allowOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, X-Requested-With')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        $response = $next($request);
        $response->headers->set('Access-Control-Allow-Origin',      $allowOrigin);
        $response->headers->set('Access-Control-Allow-Methods',     'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers',     'Content-Type, Authorization, Accept, X-Requested-With');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
