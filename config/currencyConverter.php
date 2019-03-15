<?php

return array (
    /*
    |--------------------------------------------------------------------------
    | API source
    |--------------------------------------------------------------------------
    |
    | This is date date source for the currency exchange rates. All sources except
    | 'eurocentralbank' requires a user-sign-up/account.
    |
    | https://sdw-wsrest.ecb.europa.eu/help/
    | https://openexchangerates.org
    | https://currencylayer.com
    | https://fixer.io
    |
    | Possible values: 'eurocentralbank' | 'openexchange'  | 'currencylayer' | 'fixer'
    */

    'api-source' => env('CC_API_SOURCE', 'eurocentralbank'),

    /*
    |--------------------------------------------------------------------------
    | OpenExchangeRates App ID
    |--------------------------------------------------------------------------
    |
    | Your app id from openexchangerates.org
    |
    */

    'openex-app-id' =>  env('CC_OPENEXCHANGE_APP_ID', ''),

    /*
   |--------------------------------------------------------------------------
   | CurrencyLayer API Access Key
   |--------------------------------------------------------------------------
   |
   | Your API access key for currencylayer.com
   |
   */

    'currencylayer-access-key' => env('CC_CURRENCYLAYER_ACCESS_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Fixer.io API Access Key
    |--------------------------------------------------------------------------
    |
    | Your API access key for fixer.io
    |
    */

    'fixer-access-key' => env('CC_FIXERIO_ACCESS_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Enable SSL / HTTPS
    |--------------------------------------------------------------------------
    |
    | Use ssl/https when getting data from the data sources.
    | The free version of OpenExchangeRates does NOT support https :(
    |
    */

    'use-ssl' => env('CC_USE_SSL', true),

    /*
    |--------------------------------------------------------------------------
    | OpenExchangeRates User "real" base value
    |--------------------------------------------------------------------------
    |
    | When using the free account we can still calculate other currencies based
    | on USD as a base thanks to some basic math.
    |
    | If you do not want this behavior for OpenExchangeRates set this to true
    | and 'real' base values are used instead of calculated ones. This requires
    | an 'enterprise account' on OpenExchangeRates.
    |
    */

    'openex-use-real-base' => env('CC_USE_REAL_BASE', false),

    /*
    |--------------------------------------------------------------------------
    | Fixer.io use "real" base value
    |--------------------------------------------------------------------------
    |
    | When using the free account we can still calculate other currencies based
    | on EUR as a base thanks to some basic math.
    |
    | enable this if you want real base values instead of calculated ones.
    | Requires payed account on fixer.io
    |
    */

    'fixer-use-real-base' => env('CC_USE_REAL_BASE_FIXER', false),

    /*
    |--------------------------------------------------------------------------
    | Enable Cache
    |--------------------------------------------------------------------------
    |
    | Enable the Laravel cache engine to cache the currency rates.
    |
    | https://laravel.com/docs/5.8/cache
    |
    */

    'enable-cache' => env('CC_ENABLE_CACHE', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Timeout
    |--------------------------------------------------------------------------
    |
    | Number of minutes before the cached rates expires.
    |
    | https://laravel.com/docs/5.8/cache
    |
    */

    'cache-min' => env('CC_CACHE_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Enable Detailed Log
    |--------------------------------------------------------------------------
    |
    | Log more detailed debug output like caching and when rates are fetched.
    |
    */

    'enable-log' => env('CC_ENABLE_LOG', false),

);