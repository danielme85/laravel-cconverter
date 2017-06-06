# CConverter
A simple currency conversion plug-in for Laravel 5. 
<p>
Example usage: <a href="https://danielmellum.com/#project-cconvert" target="_blank">https://danielmellum.com/#project-cconvert</a>
</p>
<p>
Please note that as Yahoo Finance has pulled plug on the historical data API, time-series are not available for Yahoo as a data source anymore.
</p>

### Installation
With composer command
```
composer require danielme85/laravel-cconverter@dev-master
```
or include under require in composer.json 
```
"danielme85/laravel-cconverter": "dev-master"
```

And like always slap a line in your config\app.php under Service Providers
```
danielme85\CConverter\CConverterServiceProvider::class,
```

If you want a Class alias add the Facade in the same config file under Class Aliases (Currency is used in this example but you can name it whatever you want)
```
'Currency'  => danielme85\CConverter\CConverter::class,
```

You need to publish the config file with the artisan command:
```
php artisan vendor:publish
```
or publish vendor config file for this plug-in only (probably cleaner).
```
php artisan vendor:publish --provider="danielme85\CConverter\CConverterServiceProvider"
```
Check the new config\CConverter.php file.
Cache is enabled per default to 60min. I recommend keeping cache enabled (per default in Laravel 5 this is file cache), expecially when using free sources, this will reduce the traffic load on these community sources.
When doing multiple conversion at the same time from the same currency the base rate will be loaded into the model (as long as you use the same model instance).   
 

### Usage

```php
use danielme85\CConverter\Currency;


//To convert a value
$valueNOK = Currency::conv($from = 'USD', $to = 'NOK', $value = 10, $decimals = 2);

//To convert a value based on historical data
$valueNOK = Currency::conv($from = 'USD', $to = 'NOK', $value = 10, $decimals = 2, $date = '2014-12-24');

//to get an array of all the rates associated to a base currency.
$rates = Currency::rates(); //defaults to USD

$rates = Currency::rates('NOK');

//Get historical rates
$rates = Currency::rates('NOK', '2014-12-24');

//Get historical rate series
$rates = Currency::rateSeries('USD', 'NOK', '2016-12-24', ''2016-12-31);
```

You can override the settings if/when you create a new instance.
```php
$currency = new Currency($api = 'yahoo', $https = false, $useCache = false, $cacheMin = 0);
```

Use the same model instance and the non-static internal convert() function when doing multiple conversion for the best performance.
(a lot faster when converting multiple values).
```php
$currency = new Currency();
$values = array (many many values).
foreach ($values as $value) {
    $valueNOK = $currency->convert($from = 'USD', $to = 'NOK', $value = 10, $decimals = 2);
}
```

Information about the current Currency object is provided by the meta() function.
```php
$currency = new Currency();
...
$info = $currency->meta();
```



Use the three lettered ISO4217 code for to/from currencies: http://en.wikipedia.org/wiki/ISO_4217

### Supported functions per API
| Config var               | API               | HTTPS         | Historical | Time Series | Sign-up required |
| ----------------- | ----------------- |:------------: | :--------: | :------------------: | :------------------: |
|openexchange | https://openexchangerates.org/ | non-free      | non-free   |  non-free            | yes |
|yahoo | Yahoo Finance     | yes          | no      |  no                | no |
|currencylayer | https://currencylayer.com/     | non-free             |  yes          |  non-free                | yes |
|fixer | http://fixer.io/     | yes             |  yes          |  no                | no |


### Version 0.0.6
* Added support for currencylayer.com, removed jsonrates.com.

### Version 0.0.5
* Added support for Yahoo finance historical date series ($currencyFrom, $currencyTo, $fromDate, $toDate). PS: if using cache and yahoo as provider, then cache should probably be cleared to avoid any conflicts with the new Yahoo time series currency data. "php artisan cache:clear" 

### Version 0.0.4
* Better handling of null returns from API's (Logs errors and converts to 0).
* Added static wrapper classes for quick "oneline" conversions (older methods should still work).
* http://jsonrates.com/ have closed down their services, replaced by: https://currencylayer.com/ limited support added, though still untested.

### Version 0.0.3
* Update to support Guzzle v6 and Laravel 5.2, thanks to @mean-cj

### Version 0.0.2-beta
* Fixed a calculation bug when using free account at openExchange.
* Added support for http://jsonrates.com (register for free to get a API key).
* Added support for historical data. Only available with http://jsonrates.com or a non-free account at http://openexchangerates.org.
Please note that there have not been implemented a proper error handler yet! 


### Disclaimer
Please take note of the Terms of Use for the different data sources.
https://policies.yahoo.com/us/en/yahoo/terms/product-atos/yql/index.htm

~~http://jsonrates.com/terms/~~

https://currencylayer.com/terms

https://openexchangerates.org/terms

This code is released per the MIT open source license: http://opensource.org/licenses/MIT
The actual rates and conversion will vary between the data sources. 
In addition I am no math professor, so you should probably not use this for super serious multi-billion dollar investments. 
If you are gonna spend your hard earned billion dollars on the money market, you should probably use something like this: http://www.forex.com/forex.html 
