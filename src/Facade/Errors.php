<?php

namespace Seaston\LaravelErrors\Facade;

use Illuminate\Support\Facades\Facade;

class Errors extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'form-errors';
    }
}
