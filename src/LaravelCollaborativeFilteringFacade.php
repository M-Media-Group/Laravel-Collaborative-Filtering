<?php

namespace MMedia\LaravelCollaborativeFiltering;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MMedia\LaravelCollaborativeFiltering\Skeleton\SkeletonClass
 */
class LaravelCollaborativeFilteringFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-collaborative-filtering';
    }
}
