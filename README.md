# CConverter
A simple currency converter plug-in for Laravel 5. Uses: http://jsonrates.com, http://openexchangerates.org and Yahoo Finance. 

### Version 0.0.2-beta
* Fixed a calculation bug when using free account at openExchange.
* Added support for http://jsonrates.com (register for free to get a API key).
* Added support for historical data. Only available with http://jsonrates.com or a non-free account at http://openexchangerates.org.
Please note that there have not been implemented a proper error handler yet! 


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

Check the new config\CConverter.php file.
Cache is enabled per default to 60min. I recommend keeping cache enabled (per default in Laravel 5 this is file cache), expecially when using free sources, this will reduce the traffic load on these community sources.
When doing multiple conversion at the same time from the same currency the base rate will be loaded into the model (as long as you use the same model instance).   
 

##Usage

```
use danielme85\CConverter\Currency;


$currency = new Currency();

//to convert a value
$valueNOK = $currency->convert($from = 'USD', $to = 'NOK', $value = 10, $decimals = 2, $date = '2014-12-24');

//When doing multiple convertions with the same from/base rate this will be loaded from the model instance. 
$values = array (many many values).
foreach ($values as $value) {
    $valueNOK = $currency->convert($from = 'USD', $to = 'NOK', $value = 10, $decimals = 2, $date = '2014-12-24');
}

//to get an array of all the rates associated to a base currency.
$rates = $currency->getRates(); //defaults to USD

$rates = $currency->getRates('NOK');
```

You can get additional information on the Currency object, like datestamp of last currency data update.
More information is provided in the array returned from the getRates() function.
```
$info = $currency->meta();
```

####Supported functions per API
| API               | HTTPS         | Historical | Custom base currency |
| ----------------- |:------------: | :--------: | :------------------: |
| JsonRates         | false         | true       |  true                |
| OpenExchangeRates | non-free      | non-free   |  non-free            |
| Yahoo Finance     | true          | false      |  true                |

Uses http://en.wikipedia.org/wiki/ISO_4217 codes.

Made in a hurry, feel free to improve :)
-dan-