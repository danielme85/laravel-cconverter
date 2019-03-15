<?php

namespace danielme85\CConverter;

use danielme85\CConverter\Providers\Rates;
use Gerardojbaez\Money\Money;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Currency
{
    public $api;
    public $cacheEnabled;
    public $cacheMinutes;
    public $fromCache = false;
    public $logEnabled = false;

    protected $provider;

    /**
     * Currency Constructor
     *
     * @param null|string $api to use (will override config if set)
     * @param null|boolean $https (true/false will override config if set)
     * @param null|boolean $useCache (true/false will override config if set)
     * @param null|integer $cacheMin (number of minutes for cache to expire will override config if set)
     * @param null|boolean $runastest Run as test and use json test data in /tests instead of actually http-rest results from API's
     *
     */
    public function __construct($api = null, $https = null, $useCache = null, $cacheMin = null, $runastest = false) {
        if (!$settings = Config::get('currencyConverter')) {
            Log::info('The currencyConverter.php config file was not found.');
        }
        //Override config/settings with constructor variables if present.
        if (isset($api)) {
            $settings['api-source'] = $api;
        }
        if (isset($https)) {
            $settings['use-ssl'] = $https;
        }
        if (isset($useCache)) {
            $settings['enable-cache'] = $useCache;
        }
        if (isset($cacheMin)) {
            $settings['cache-min'] = $cacheMin;
        }

        $settings['runastest'] = $runastest;
        $this->api = $settings['api-source'];
        $this->cacheEnabled = $settings['enable-cache'];
        $this->cacheMinutes = $settings['cache-min'];
        $this->logEnabled = $settings['enable-log'];

        try {
            $this->provider = CurrencyProvider::getProvider($this->api, $settings);
        } catch (\Exception $e) {

        }
    }

    /**
     * Get currency rates in an array format
     *
     * @param null|string $base The base currency (defaults to USD if null/empty).
     * @param null|string $date The date (defaults to today if null/empty).
     *
     * @return array
     */
    public function getRates($base = null, $date = null) : array
    {
        $rates = $this->getRateModel($base, $date);

        return $rates->toArray();
    }

    /**
     * Get currency rates in an array format
     *
     * @param null|string $base The base currency (defaults to USD if null/empty).
     * @param null|string $date The date (defaults to today if null/empty).
     *
     * @return array
     */
    public function getRateResults($base = null, $date = null) : array
    {
        $rates = $this->getRateModel($base, $date);

        return $rates->rates;
    }

    /**
     * Get the Rates object from the Provider
     *
     * @param null|string $base
     * @param null|string $date
     *
     * @return Rates
     */
    public function getRateModel($base = null, $date = null) : Rates
    {
        $base = strtoupper($base);
        if (empty($base)) {
            $base = 'USD';
        }
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        $this->fromCache = false;
        $api = $this->api;

        if ($this->cacheEnabled) {
            $cachekey = "cc-$api-$base-$date";

            $rates = Cache::get($cachekey);
            if (is_object($rates) and isset($rates->rates)) {
                $this->fromCache = true;
                if ($this->logEnabled) {
                    Log::debug("Got currency rates from cache: $cachekey");
                }
            }
            else {
                $rates = $this->provider->rates($base, $date);
                if (!empty($rates->rates)) {
                    if(Cache::add($cachekey, $rates, $this->cacheMinutes) and $this->logEnabled) {
                        Log::debug('Added new currency rates to cache: '.$cachekey.' for '.$this->cacheMinutes.' min.');
                    }
                }
            }
        }
        else {
            $rates = $this->provider->rates($base, $date);
        }

        if (empty($rates->rates)) {
            Log::warning("$rates->error -> $rates->url");
        }

        return $rates;
    }


    /**
     * Convert a from one currency to another
     *
     * @param string $from ISO4217 country code
     * @param string $to ISO4217 country code
     * @param mixed $value calculate from this number
     * @param integer|string $round round this this number of decimals or use money formatter.
     * @param null|string $date date for historical data
     *
     * @return float|string $result
     */
    public function convert($from, $to, $value, $round = 2, $date = null)
    {
        $result = 0;
        $from = strtoupper($from);
        $to = strtoupper($to);

        if (empty($value)) {
            return $result;
        }

        $rates = $this->getRateResults($from, $date);

        if (!empty($rates)) {
            if (!array_key_exists($to, $rates)) {
                Log::warning("The currency $to does not exist for the provider: $this->api");
                return $result;
            }
            $rate = $rates[$to];

            if ($from === 'USD') {
                //All currencies are stored in the model has USD as base currency
                $result = $value * $rate;
            }
            else {
                //Convert Currencies via USD
                $result = $rate * ($value/$rates[$from]);
            }

            if ($round === 'money') {
                $result = moneyFormat($result, $to);
            }
            else if ($round or $round === 0) {
                $result = round($result, $round);
            }
        }
        else {
            Log::warning("No rates for $from found from provider: $this->api");
            return $result;
        }
        return $result;
    }

    /**
     * Convert a from one currency to another
     *
     * @param string $from ISO4217 country code
     * @param string $to ISO4217 country code
     * @param mixed $value calculate from this number
     * @param null|integer|string $round round this this number of decimals, or use 'money' for money formatter.
     * @param null|string $date date for historical data
     * @param null|string $api override Provider setting,
     * @param null|bool $https override https setting
     * @param null|bool $useCache override cache setting
     * @param null|int $cacheMin override cache setting
     * @param null|bool $runastest for testing, uses local test data.
     *
     * @return mixed $result
     */
    public static function conv($from, $to, $value, $round = null, $date = null, $api = null, $https = null,
                                $useCache = null, $cacheMin = null, $runastest = false)
    {
        $convert = new self($api, $https, $useCache, $cacheMin, $runastest);
        return $convert->convert($from, $to, $value, $round, $date);
    }


    /**
     * Get rates for given currency an optional date, defaults to USD
     *
     * @param null|string $base
     * @param null|string $date
     * @param null|string $api override Provider setting
     * @param null|bool $https override https setting
     * @param null|bool $useCache override cache setting
     * @param null|int $cacheMin override cache setting
     * @param null|bool $runastest for testing, uses local test data.
     *
     * @return array
     */
    public static function rates($base = null, $date = null, $api = null, $https = null,
                                 $useCache = null, $cacheMin = null, $runastest = false) : array
    {
        $rates = new self($api, $https, $useCache, $cacheMin, $runastest);
        return $rates->getRateResults($base, $date);
    }


    /**
     * Get the provider model instance
     *
     * @return Providers\CurrencyLayer|Providers\EuropeanCentralBank|Providers\Fixer|Providers\OpenExchange
     */
    public function provider() {
        return $this->provider;
    }

    /**
     * Money formatter
     *
     * @param int $amount
     * @param string $currency
     *
     * @return Money
     */
    public static function money($amount = 0, $currency = 'USD') : Money
    {
        return new Money($amount, $currency);
    }
}
