<?php

namespace Ndberg\IntrospectionClient;

use Illuminate\Support\Facades\Facade;

/**
 *
 */
class IntrospectionFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'introspection';
    }
}
