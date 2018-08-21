<?php

namespace danielme85\CConverter;

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
     *
     * @param string $api to use (will override config if set)
     * @param boolean $https (true/false will override config if set)
     * @param boolean $useCache (true/false will override config if set)
     * @param integer $cacheMin (number of minutes for cache to expire will override config if set)
     * @param boolean $runastest Run as test and use json test data in /tests instead of actually http-rest results from API's
     *
     */
    public function __construct($api = null, $https = null, $useCache = null, $cacheMin = null, $runastest = false) {
        if (!$settings = Config::get('CConverter')) {
            Log::info('The CConverter.php config file was not found.');
        }
        //Override config/settings with constructor variables if present.
        if (isset($api)) {
            $settings['api-source'] = $api;
        }
        if (isset($https)) {
            $settings['use-https'] = $https;
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

    public function getRates($base = null, $date = null) : array
    {
        $base = strtoupper($base);
        if (empty($base)) {
            $base = 'USD';
        }
        $this->fromCache = false;
        $api = $this->api;

        if ($this->cacheEnabled === true) {
            if ($rates = Cache::get("CConverter$api$base$date")) {
                $this->fromCache = true;
                if ($this->logEnabled) {
                    Log::debug("Got currency rates from cache: CConverter$api$base$date");
                }
            }
            else {
                $rates = $this->provider->rates($base, $date);
                if ($rates) {
                    if(Cache::add("CConverter$api$base$date", $rates, $this->cacheMinutes) and $this->logEnabled) {
                        Log::debug('Added new currency rates to cache: CConverter'.$api.$base.$date.' - for '.$this->cacheMinutes.' min.');
                    }
                }
            }
        }
        else {
            $rates = $this->provider->rates($base, $date);
        }

        return $rates;
    }


    /*
     * Get the current rates.
     *
     * @param string $base the Currency base (will override config if set)
     * @param string $date for historical data.
     *
     * @return object returns a GuzzleHttp\Client object.
     */
    public function getRatesOLD($base = null, $date = null) {

        //if there is no base spesified it will default to USD.
        //Also for the free OpenExchange account there is no support for change of base currency.
        if (!isset($base) or (!$this->settings['openex-use-real-base'] and $this->settings['api-source'] === 'openexchange')) {
            $base = 'USD';
            $this->base = $base;
        }
        else {
            $this->base = $base;
        }

        $this->date = $date;
        $api = $this->settings['api-source'];

        if ($this->settings['enable-cache']) {
            if ($result = Cache::get("CConverter$api$base$date")) {

                $this->fromCache = true;
                if (Config::get('CConverter.enable-log')) {
                    Log::debug("Got currency rates from cache: CConverter$api$base$date");
                }
            }
            else {
                if ($api === 'yahoo') {
                    $result = $this->yahoo();
                }
                else if ($api === 'openexchange') {
                    $result = $this->openExchange();
                }

                else if ($api === 'currencylayer') {
                    $result = $this->jsonRates();
                }

                else if ($api === 'fixer') {
                    $result = $this->fixer();
                }

                if ($result) {
                    if(Cache::add("CConverter$api$base$date", $result, $this->settings['cache-min']) and Config::get('CConverter.enable-log')) {
                        Log::debug('Added new currency rates to cache: CConverter'.$api.$base.$date.' - for '.$this->settings['cache-min'].' min.');
                    }
                }
                $this->fromCache = false;
            }
        }
        else {
            if ($api === 'yahoo') {
                $result = $this->yahoo();
            }
            else if ($api === 'openexchange') {
                $result = $this->openExchange();
            }
            else if ($api === 'currencylayer') {
                $result = $this->jsonRates();
            }
            else if ($api === 'fixer') {
                $result = $this->fixer();
            }

            $this->fromCache = false;
        }

        return $result;
    }

   /**
    * Get a RateSeries (not supported by OpenExchange or fixer.io)
    *
    * @param string $from
    * @param string $to
    * @param string $dateStart
    * @param string $dateEnd
    *
    *  @return object returns a GuzzleHttp\Client object.
    */
    public function getRateSeriesOLD($from, $to, $dateStart, $dateEnd) {
        $api = $this->settings['api-source'];
        if ($this->settings['enable-cache']) {
            if (Cache::has("CConverter$from$to$dateStart$dateEnd")) {
                $result = Cache::get("CConverter$from$to$dateStart$dateEnd");
                $this->fromCache = true;
                if (Config::get('CConverter.enable-log')) {
                    Log::debug("Got currency rates from cache: CConverter$from$to$dateStart$dateEnd");
                }
            }
            else {
                if ($api === 'yahoo') {
                    $result = $this->yahooTimeSeries($from, $to, $dateStart, $dateEnd);
                }
                else if ($api === 'openexchange') {
                    Log::error('Openexchange does not support currency rate time-series.');
                    return null;
                }
                else if ($api === 'fixer') {
                    Log::error('Fixer.io does not support currency rate time-series.');
                    return null;
                }
                else if ($api === 'currencylayer') {
                    $result = $this->jsonRatesTimeSeries($from, $to, $dateStart, $dateEnd);
                }

                Cache::add("CConverter$from$to$dateStart$dateEnd", $result, $this->settings['cache-min']);
                $this->fromCache = false;

                if (Config::get('CConverter.enable-log')) {
                    Log::debug('Added new currency rates to cache: CConverter'.$api.$from.$to.$dateStart.$dateEnd.' - for '.$this->settings['cache-min'].' min.');
                }
            }
        }
        else {
            if ($api === 'yahoo') {
                $result = $this->yahooTimeSeries($from, $to, $dateStart, $dateEnd);
            }
            else if ($api === 'openexchange') {
                Log::error('Openexchange does not support currency rate time-series.');
                return null;
            }
            else if ($api === 'fixer') {
                Log::error('Fixer.io does not support currency rate time-series.');
                return null;
            }
            else if ($api === 'currencylayer') {
                $result = $this->jsonRatesTimeSeries($from, $to, $dateStart, $dateEnd);
            }

            $this->fromCache = false;
        }

        return $result;
    }

    /**
     * Convert a from one currency to another
     *
     * @param string $from ISO4217 country code
     * @param string $to ISO4217 country code
     * @param mixed $value calculate from this number
     * @param integer $round round this this number of desimals.
     * @param string $date date for historical data
     *
     * @return float $result
     */
    public function convertOLD($from, $to, $value, $round = null, $date = null) {
        $result = array();

        if ($value === 0 or $value === null or $value === '' or empty($value)) {
            return 0;
        }

        //A special case for openExchange free version.
        if (!$this->settings['openex-use-real-base'] and $this->settings['api-source'] === 'openexchange') {
            $base = 'USD';
        }
        else {
            $base = $from;
        }

        //Check if base currency is already loaded in the model
        if ($this->base === $base and $this->date === $date) {
            $rates = $this->rates;
        }
        //If not get the needed rates
        else {
            $rates = $this->getRates($from, $date);
            $this->rates = $rates;
        }

        if (isset($this->rates['rates'])) {
            //A special case for openExchange.
            if ($from === 'USD' and !$this->settings['openex-use-real-base'] and $this->settings['api-source'] === 'openexchange') {
                $result = $value * $rates['rates'][$to];
            }

            //A special case for openExchange.
            else if ($to === 'USD' and !$this->settings['openex-use-real-base'] and $this->settings['api-source'] === 'openexchange') {
                $result = $value / $rates['rates'][$from];
            }

            //When using openExchange free version we can still calculate other currencies trough USD.
            //Hope this math is right :)
            else if (!$this->settings['openex-use-real-base'] and $this->settings['api-source'] === 'openexchange'){
                $to_usd = $rates['rates'][$to];
                $from_usd = $rates['rates'][$from];
                $result =  $to_usd * ($value/$from_usd);
            }

            //Use actual base currency to calculate.
            else {
                $result = $value * $rates['rates'][$to];
            }

            if ($round or $round === 0) {
                $result = round($result, $round);
            }

        }

        return $result;
    }

    public function convert($from, $to, $value, $round = 2, $date = null) {
        $result = 0;
        $from = strtoupper($from);
        $to = strtoupper($to);

        if (empty($value)) {
            return $result;
        }

        $provider = $this->provider->rates($from, $date);

        if (!empty($provider['rates'])) {
            $rates = $provider['rates'];
            if (!array_key_exists($to, $rates)) {
                Log::warning("The currency $to does not exist for the provider $this->provider->name ");
                return $result;
            }

            if ($from === 'USD') {
                //All currencies are stored in the model has USD as base currency
                $result = $rates[$to];
            }
            else {
                //Convert Currencies via USD
                $result = $rates[$to] * ($value/$rates[$from]);
            }




            if ($round or $round === 0) {
                $result = round($result, $round);
            }
        }
        else {
            //no rates
        }
        return $result;
    }

    /**
     * Convert a from one currency to another
     *
     * @param string $from ISO4217 country code
     * @param string $to ISO4217 country code
     * @param mixed $int calculate from this number
     * @param integer $round round this this number of desimals.
     * @param string $date date for historical data
     * @param string $api override Provider setting
     * @param bool $https override https setting
     * @param bool $useCache override cache setting
     * @param int $cacheMin override cache setting
     * @param bool $runastest for testing, uses local test data.
     *
     * @return mixed $result
     */
    public static function conv($from, $to, $int = 0, $round = null, $date = null, $api = null, $https = null, $useCache = null, $cacheMin = null, $runastest = false) {
        $convert = new self($api, $https, $useCache, $cacheMin, $runastest);
        return $convert->convert($from, $to, $int, $round, $date);
    }


    /**
     * Get rates for given currency an optional date, defaults to USD
     *
     * @param null $base
     * @param null $date
     * @param string $api override Provider setting
     * @param bool $https override https setting
     * @param bool $useCache override cache setting
     * @param int $cacheMin override cache setting
     * @param bool $runastest for testing, uses local test data.
     *
     * @return array
     */
    public static function rates($base = null, $date = null, $api = null, $https = null, $useCache = null, $cacheMin = null, $runastest = false) {
        $rates = new self($api, $https, $useCache, $cacheMin, $runastest);
        return $rates->getRates($base, $date);
    }

    /**
     * Get a rate series for given to/from currency and dates
     *
     * @param string $from
     * @param string $to
     * @param string $start
     * @param string $end
     * @return object
     */
    public static function rateSeries($from, $to, $start, $end) {
        $rates = new self;
        return $rates->getRateSeries($from, $to, $start, $end);
    }

    /**
     * Returns array of metadata stored as object attributes for current instance of CConvert
     *
     * @return array
     */

    public function meta() {
        return $this->provider->meta();
    }
}
