<?php

namespace App\Http\Middleware;

use App\Support\XamppAutoSetup;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureXamppSetup
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            XamppAutoSetup::ensureInstalled(app());
        } catch (Throwable $exception) {
            return XamppAutoSetup::setupErrorResponse($exception);
        }

        return $next($request);
    }
}
