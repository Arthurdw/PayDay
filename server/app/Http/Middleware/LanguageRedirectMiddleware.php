<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LanguageRedirectMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/*')) {
            $segments = $request->segments();
            $segments = array_merge(['en'], $segments);
            $newUrl = $request->getSchemeAndHttpHost() . '/' . implode('/', $segments);

            $newRequest = Request::create($newUrl, $request->getMethod(), $request->toArray(), $request->cookies->all(), $request->files->all(), $request->server->all());
            $newRequest->headers = $request->headers;

            return $next($newRequest);
        }

        return $next($request);
    }
}
