<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $allowedOrigins = [
            'https://notnew.apextechworldllc.com/',
            'http://127.0.0.1',
            'http://front.letsdeploy.us',
            'https://front.letsdeploy.us',
            'https://NotNew.com',
            'https://api.NotNew.com'
        ];
        $requestOrigin = $request->headers->get('origin');

        if (in_array($requestOrigin, $allowedOrigins)) {
            return $next($request)
                ->header('Access-Control-Allow-Origin', $requestOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        return $next($request);
    }
}
