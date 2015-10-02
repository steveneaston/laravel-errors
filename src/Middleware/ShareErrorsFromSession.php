<?php

namespace Seaston\LaravelErrors\Middleware;

use Closure;

class ShareErrorsFromSession
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
        // Load errors from the session into Seaston\LaravelErrors\ViewErrorBag
        app('form-errors')->make($request);

        // Putting the errors in the view for every view allows the developer to just
        // assume that some errors are always available, which is convenient since
        // they don't have to continually run checks for the presence of errors.

        return $next($request);
    }
}
