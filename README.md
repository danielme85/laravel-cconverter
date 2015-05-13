# CConverter
A simple currency converter plugin for Laravel 5. Uses: http://openexchangerates.org and Yahoo Finance. 

### Version 0.0.1-beta
* Added support for use of Yahoo data in addition to openExchangeRates. 
* The free account at http://openexchangerates.org limits to USD as base value, added some simple math to convert from non-usd to non-usd by calculating from USD values (this behavior can be controller in config). 
* Some mayor changes in the config file so make sure you publish a new config if upgrading. 

##Installation
require in composer.json 
```
"danielme85/laravel-cconverter": "dev-master"
```

And like always slap a line in your config\app.php under Service Providers
```
'danielme85\CConverter\CConverterServiceProvider'
```

You need to publish the config file with the artisan command:
```
php artisan vendor:publish
```

Check the new config\CConverter.php file, you need to set a app key from http://openexchangerates.org if you want to use that api source.
Per default it is set to use the Yahoo finance API.
Cache is enabled per default to 60min, the free account at openExchange updates once an hour, I have no idea how often the Yahoo one updates.

##Usage

```
use danielme85\CConverter\Currency;


$currency = new Currency();

//to convert a value
$valueNOK = $currency->convert($from = 'USD', $to = 'NOK', $value = 10, $decimals = 2);

//to get an array of all the rates associated to a base currency.
$rates = $currency->getRates(); //defaults to USD

//please note that this will only work on non-free subscriptions on openexchangerates.org or when using Yahoo (default).
$rates = $currency->getRates('NOK');
```

You can get additional information on the Currency object, like datestamp of last currency data update.
More information is provided in the array returned from the getRates() function.
```
$info = $currency->meta();
```

Uses http://en.wikipedia.org/wiki/ISO_4217 codes.

Made in a hurry, feel free to improve :)
