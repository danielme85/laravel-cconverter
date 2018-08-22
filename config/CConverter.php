<?php

/* 
 * The MIT License
 *
 * Copyright 2015 Daniel Mellum.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

return array (
    
    //API source. 
    //Possible values: 'eurocentralbank' | 'openexchange' | 'yahoo' | 'currencylayer' | 'fixer'
    'api-source' => env('CC_API_SOURCE', 'eurocentralbank'),

    //Your app id from openexchangerates.org
    'openex-app-id' =>  env('CC_OPENEXCHANGE_APP_ID', ''),

    //your API access key for currencylayer.com
    'currencylayer-access-key' => env('CC_CURRENCYLAYER_ACCESS_KEY', ''),

    //your API access key for fixer.io
    'fixer-access-key' => env('CC_FIXERIO_ACCESS_KEY', ''),

    //use https? the free version of openexchange and jsonrates does not support https :(
    'use-ssl' => env('CC_USE_SSL', true),

    //When using the free account we can still calculate other currencies based on USD as a base thanks to some basic math.
    //enable this if you want real base values instead of calculated ones. Requires enterprise account from openexchangerates.org
    'openex-use-real-base' => env('CC_USE_REAL_BASE', false),

    //When using the free account we can still calculate other currencies based on EUR as a base thanks to some basic math.
    //enable this if you want real base values instead of calculated ones. Requires payed account on fixer.io
    'fixer-use-real-base' => env('CC_USE_REAL_BASE_FIXER', false),

    //use Laravel cache engine to cache the results.
    'enable-cache' => env('CC_ENABLE_CACHE', true),

    //minutes cache should expire.
    'cache-min' => env('CC_ENABLE_CACHE',60),

    //use Laravel detailed logging
    'enable-log' => env('CC_ENABLE_LOG',false),

);