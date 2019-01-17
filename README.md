# Laravel Currency Converter

[![GitHub](https://img.shields.io/github/license/mashape/apistatus.svg?style=flat-square)](https://github.com/danielme85/laravel-cconverter)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/danielme85/laravel-cconverter.svg?style=flat-square)](https://packagist.org/packages/danielme85/laravel-cconverter)
[![GitHub release](https://img.shields.io/github/release/danielme85/laravel-cconverter.svg?style=flat-square)](https://packagist.org/packages/danielme85/laravel-cconverter)
[![GitHub tag](https://img.shields.io/github/tag/danielme85/laravel-cconverter.svg?style=flat-square)](https://github.com/danielme85/laravel-cconverter)
[![Travis (.org)](https://img.shields.io/travis/danielme85/laravel-cconverter.svg?style=flat-square)](https://travis-ci.org/danielme85/laravel-cconverter)
[![Codecov](https://img.shields.io/codecov/c/github/danielme85/laravel-cconverter.svg?style=flat-square)](https://codecov.io/gh/danielme85/laravel-cconverter)

A simple currency conversion plug-in for Laravel 5.5+ 💵<br>
Example usage: <a href="https://danielmellum.com/projects/currency-converter" target="_blank">https://danielmellum.com/projects/currency-converter</a>

Version testing and requirements

| Version        | Tested with   |
| :----------:   |:-------------:| 
| v0.2.*         | Laravel 5.6   | 
| v0.1.*         | Laravel 5.5   | 
| v0.0.7         | Laravel 5.4   | 

<small>If you are having composer requirement issues using the latest release and Laravel < v5.4, try the v0.0.7 release.</small>

Please note:
* Yahoo Finance has pulled the plug on the historical data API, time-series are not available for Yahoo as a data source anymore.
* Fixer.io has gone to the dark side and requires a sign-up now. You only get 1000 req per month, I don't recommend using this...
unless you leverage cache... or maybe even store a model of the response data. You would need to create your own model and migration
as needed. Note that storing data for more then temporary cache might go against data providers user terms.

### Installation
```
composer require danielme85/laravel-cconverter
```

### Configuration 
You can publish this vendor config file if you would like to make changes to the default config.
```
php artisan vendor:publish --provider="danielme85\CConverter\CConverterServiceProvider"
```

### Usage
There are static class "shortcuts" to convert or get one-time Currency series. 
```php
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

#### Working with multiple values
I would highly recommend creating a model instance and the non-static functions getRates() & convert() when doing 
multiple conversion or getting multiple currency series for the best performance. The currency rates are stored 
in the provider model by date/base-currency for quick and easy access. 

```php
$currency = new Currency();
$values = [1, 3, 4, 5...].
foreach ($values as $value) {
    $valueNOK = $currency->convert($from = 'USD', $to = 'NOK', $value = 10, $decimals = 2);
}

$rates = $currency->getRates('NOK');
foreach ($rates as $rate) {
    $value = $valueNOK * $rate;
}
```

You can override the settings when/if you create a new instance.
```php
$currency = new Currency(
    $api = 'yahoo', 
    $https = false, 
    $useCache = false, 
    $cacheMin = 0);
...
$result = Currency:conv(
    $from = 'USD', 
    $to = 'NOK', 
    $value = 10, 
    $decimals = 2, 
    $date = '2014-12-24', 
    $api = 'yahoo', 
    $https = false, 
    $useCache = false, 
    $cacheMin = 0);
```

Use the three lettered ISO4217 code for to/from currencies: http://en.wikipedia.org/wiki/ISO_4217

#### Money Formatting
The package: gerardojbaez/money is included for an easier and more powerful Money Formatter, excellent alternative to money_format().
You can get the values of an conversion by setting round='money' (money formatter overrides rounding).
```php
Currency::conv('USD', 'USD', 10, 2);
//Result: 10
Currency::conv('USD', 'USD', 10, 'money');
//Result: $10.00
$currency->convert('USD', 'USD', 10, 'money');
//Result: $10.00
```
You can also get the money formatter itself trough the static Currency function:
```php
$formater = Currency::money($amount = 0, $currency = 'USD');
```
This Money Formatter also ships with a handy helper function.
```php
echo moneyFormat(10, 'USD');
//Result: $10.00

```
See Money Formatter github page for more information and usage.
https://github.com/gerardojbaez/money

### Supported functions per API
Default API is: The European Central Bank

| Config var        | API                           | HTTPS         | Historical    |  Sign-up required |
| ----------------- | --------------------------    |:------------: | :---------:   |  :--------------: |
|eurocentralbank    | The European Central Bank     | yes           | yes           |   no              |
|openexchange       | https://openexchangerates.org | non-free      | non-free      |   yes             |
|* ~~yahoo~~        | ~~Yahoo Finance~~             | ~~yes~~       | ~~no~~        |   ~~no~~          |
|currencylayer      | https://currencylayer.com/    | non-free      | yes           |   yes             |
|fixer              | http://fixer.io/              | yes           | yes           |   yes             |

<i>*Yahoo has discontinued their finance data API.</i>

### Disclaimer
Please take note of the Terms of Use for the different data sources.
<br>
https://policies.yahoo.com/us/en/yahoo/terms/product-atos/yql/index.htm
<br>
https://currencylayer.com/terms
<br>
https://openexchangerates.org/terms

This code is released per the MIT open source license: http://opensource.org/licenses/MIT
The actual rates and conversion will vary between the data sources. 
In addition I am no math professor, so you should probably not use this for super serious multi-billion dollar investments. 
If you are gonna spend your hard earned billion dollars on the money market, you should probably use something like this: http://www.forex.com/forex.html 
