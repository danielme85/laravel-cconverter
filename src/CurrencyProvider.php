<?php
/**
 * Created by Daniel Mellum <mellum@gmail.com>
 * Date: 8/18/2018
 * Time: 10:29 AM
 */

namespace danielme85\CConverter;

use danielme85\CConverter\Providers\Fixer;
use danielme85\CConverter\Providers\OpenExchange;
use danielme85\CConverter\Providers\Yahoo;

class CurrencyProvider
{

    public static function getProvider($apiSource)
    {
        if ($apiSource=== 'yahoo') {
            return new Yahoo();
        }
        else if ($apiSource === 'openexchange') {
            return new OpenExchange();
        }
        else if ($apiSource === 'currencylayer') {
            return new CurrencyLayer();
        }
        else if ($apiSource === 'fixer') {
            return new Fixer();
        }
    }


}