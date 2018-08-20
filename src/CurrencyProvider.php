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
use danielme85\CConverter\Providers\Yahoo;

class CurrencyProvider
{

    public static function loadProvider($apiSource)
    {
        $providerCache = new CurrencyProviderCache();

        if ($apiSource === 'eurocentralbank') {
            if (!empty($instance = $providerCache->getInstance($apiSource))) {
                if (get_class($instance) === 'EuropeanCentralBank') {
                    return $instance;
                }
            }
            $instance = new EuropeanCentralBank();
            $providerCache->setInstance($apiSource, $instance);
            return $instance;
        }
        else if ($apiSource=== 'yahoo') {
            if (!empty($providerCache)) {
                if (get_class($this->providerCache) === 'Yahoo') {
                    return $this->providerCache;
                }
            }
            return new Yahoo();
        }
        else if ($apiSource === 'openexchange') {
            if (!empty($providerCache)) {
                if (get_class($this->providerCache) === 'OpenExchange') {
                    return $this->providerCache;
                }
            }
            return new OpenExchange();
        }
        else if ($apiSource === 'currencylayer') {
            if (!empty($providerCache)) {
                if (get_class($this->providerCache) === 'CurrencyLayer') {
                    return $this->providerCache;
                }
            }
            return new CurrencyLayer();
        }
        else if ($apiSource === 'fixer') {
            if (!empty($providerCache)) {
                if (get_class($this->providerCache) === 'Fixer') {
                    return $this->providerCache;
                }
            }
            return new Fixer();
        }
    }


}