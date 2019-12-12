<?php namespace EloquentEs;

use Illuminate\Support\Facades\Facade;

/**
 * Class EloquentEsFacade
 * @package EloquentEs
 */
class EloquentEsFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ees.elastic';
    }
}