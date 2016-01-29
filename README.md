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
(Laravel 5.1+) 'danielme85\CConverter\CConverterServiceProvider::class'
```

If you want a Class alias add the Facade in the same config file under Class Aliases (Currency is used in this example but you can name it whatever you want)
```
'Currency'  => 'danielme85\CConverter\CConverter',
(Laravel 5.1+) 'Currency'  => danielme85\CConverter\CConverter::class,
```

You need to publish the config file with the artisan command:
```
php artisan vendor:publish
```

Check the new config\CConverter.php file.
Cache is enabled per default to 60min. I recommend keeping cache enabled (per default in Laravel 5 this is file cache), expecially when using free sources, this will reduce the traffic load on these community sources.
When doing multiple conversion at the same time from the same currency the base rate will be loaded into the model (as long as you use the same model instance).   
 

##Usage

```php
use danielme85\CConverter\Currency;
// or "use Currency" (if you added the class alias and facade).
// if you did add the facade you can shorten everything: ex $value = Currency::convert($from, $to, $value, $decimals), $rates = Currency::getRates().


$currency = new Currency();

//To convert a value
$valueNOK = $currency->convert($from = 'USD', $to = 'NOK', $value = 10, $decimals = 2);

//To convert a value based on historical data
$valueNOK = $currency->convert($from = 'USD', $to = 'NOK', $value = 10, $decimals = 2, $date = '2014-12-24');

//Use the same model instance for multiple conversion for the best performance. 
$values = array (many many values).
foreach ($values as $value) {
    $valueNOK = $currency->convert($from = 'USD', $to = 'NOK', $value = 10, $decimals = 2);
}

//to get an array of all the rates associated to a base currency.
$rates = $currency->getRates(); //defaults to USD

$rates = $currency->getRates('NOK');

//Get the historical rates
$rates = $currency->getRates('NOK', '2014-12-24');
```

Information about the current Currency object is provided by the meta() function.
```php
$info = $currency->meta();
```

Use the three lettered ISO4217 code for to/from currencies: http://en.wikipedia.org/wiki/ISO_4217

####Supported functions per API
| API               | HTTPS         | Historical | Custom base currency |
| ----------------- |:------------: | :--------: | :------------------: |
| JsonRates         | false         | true       |  true                |
| OpenExchangeRates | non-free      | non-free   |  non-free            |
| Yahoo Finance     | true          | false      |  true                |


##Disclaimer
Please take note of the Terms of Use for the different data sources.
https://policies.yahoo.com/us/en/yahoo/terms/product-atos/yql/index.htm
http://jsonrates.com/terms/
https://openexchangerates.org/terms

This code is released per the MIT open source license: http://opensource.org/licenses/MIT
The actual rates and conversion will vary between the data sources. 
In addition I am no math professor, so you should probably not use this for super serious multi-billion dollar investments. 
If you are gonna spend your hard earned billion dollars on the money market, you should probably use something like this: http://www.forex.com/forex.html 
