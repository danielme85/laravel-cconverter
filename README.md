# CConverter
A simple currency converter plugin for Laravel 5. Uses: http://openexchangerates.org

##Innstalation
require in composer.json 
```
"danielme85/laravel-cconverter": "dev-master"
```

And like always slap a line in your config\app.php under Service Providers
```
'danielme85\CConverter\CConverterServiceProvider'
```

You need to public the config file with the artisan command:
```
php artisan vendor:publish
```

Check the new config\CConverter.php file, you need to set a app key from http://openexchangerates.org.
Cache is enabled per default to 60min, as the data source is only updated once an hour (at least the free version).

##Usage

```
use danielme85\CConverter\Currency;

$valueUSD = 1;
$decimals = 2;

$currency = new Currency();
$valueNOK = $currency->convert('USD', 'NOK', $valueUSD, $decimals);

```

You can get additional information in the Currency object, like datestamp of last currency data update. 
```
$info = $currency->meta();
```

Uses http://en.wikipedia.org/wiki/ISO_4217 codes.

Made in a hurry, feel free to improve :)