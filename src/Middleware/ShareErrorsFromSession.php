<?php

namespace Seaston\LaravelErrors\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Seaston\LaravelErrors\ViewErrorBag;

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
        // Load errors from the session into ViewErrorBag
        App::make(ViewErrorBag::class)->make($request);

        // Putting the errors in the view for every view allows the developer to just
        // assume that some errors are always available, which is convenient since
        // they don't have to continually run checks for the presence of errors.
        return $next($request);
    }
}
