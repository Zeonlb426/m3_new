<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class VerifyAuth
{

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (\Auth::guard('sanctum')->check()) {
            \Auth::shouldUse('sanctum');
        }

        return $next($request);
    }

}
