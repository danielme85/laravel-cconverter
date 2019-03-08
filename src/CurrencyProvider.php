<?php
/**
 * Created by Daniel Mellum <mellum@gmail.com>
 * Date: 8/18/2018
 * Time: 10:29 AM
 */

namespace danielme85\CConverter;

use danielme85\CConverter\Providers\EuropeanCentralBank;
use danielme85\CConverter\Providers\Fixer;
use danielme85\CConverter\Providers\OpenExchange;
use danielme85\CConverter\Providers\CurrencyLayer;

class CurrencyProvider
{

    /**
     * @param string $apiSource 'eurocentralbank'|'openexchange'|'currencylayer'|'fixer'
     * @param array $settings
     * @return CurrencyLayer|EuropeanCentralBank|Fixer|OpenExchange
     * @throws \Exception
     */
    public static function getProvider($apiSource, $settings)
    {
        if ($apiSource === 'eurocentralbank') {
            return new EuropeanCentralBank($settings);
        }
        else if ($apiSource === 'openexchange') {
            return new OpenExchange($settings);
        }
        else if ($apiSource === 'currencylayer') {
            return new CurrencyLayer($settings);
        }
        else if ($apiSource === 'fixer') {
            return new Fixer($settings);
        }
        else {
            throw new \Exception("No suitable data provider found for Currency Converter", 500);
        }
    }


}