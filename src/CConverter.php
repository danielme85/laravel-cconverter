<?php

namespace danielme85\CConverter;

use Illuminate\Support\Facades\Facade;

/**
 * Description of CConverterFacade
 *
 * @author dmellum
 */
class CConverter extends Facade {
    protected static function getFacadeAccessor()
    {
        return 'Currency';
    }
}
