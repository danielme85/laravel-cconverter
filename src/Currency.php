<?php namespace danielme85\CConverter;
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

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class Currency {

    private $settings, $requestUrl, $base, $rates, $fromCache, $date;


    /*
     *
     * @param string $api to use (will override config if set)
     * @param boolean $https (true/false will override config if set)
     * @param boolean $useCache (true/false will override config if set)
     * @param integer $cacheMin (number of minutes for cache to expire will override config if set)
     *
     */
    public function __construct($api = null, $https = null, $useCache = null, $cacheMin = null) {
        $this->settings = Config::get('CConverter');
        if (!is_array($this->settings)) {
            Log::error('The CConverter config file is needed. Did you run: php artisan vendor:publish ?');
        }

        if (isset($api)) {
            $this->settings['api-source'] = $api;
        }
        if (isset($https)) {
            $this->settings['use-https'] = $https;
        }
        if (isset($useCache)) {
            $this->settings['enable-cache'] = $useCache;
        }
        if (isset($cacheMin)) {
            $this->settings['cache-min'] = $cacheMin;
        }
    }

    protected function openExchange() {
        $base = $this->base;
        $date = $this->date;

        if ($this->settings['use-ssl']) {
            $url = 'https';
        }
        else {
            $url = 'http';
        }

        if (isset($date)) {
            $url .= '://openexchangerates.org/api/time-series.json?app_id=' . $this->settings['openex-app-id'] .'&start='.$date.'&end='.$date.'&base='.$base;
        }
        else {
            $url .= '://openexchangerates.org/api/latest.json?app_id=' . $this->settings['openex-app-id'] .'&base='.$base;
        }

        $this->requestUrl = $url;

        $client = new Client();
        $response = $client->get($url);

        return $this->convertFromOpenExchange(json_decode($response->getBody(),true));
    }

    protected function jsonRates() {
        $base = $this->base;
        $date = $this->date;

        if ($this->settings['use-ssl']) {
            $url = 'https';
        }
        else {
            $url = 'http';
        }

        if (isset($date)) {
            $url .= '://apilayer.net/api/historical/?acces_key='.$this->settings['jsonrates-app-id'].'&date='.$date.'&source='.$base;
        }
        else {
            $url .= '://apilayer.net/api/live/?acces_key='.$this->settings['jsonrates-app-id'].'&source='.$base;
        }

        $this->requestUrl = $url;
        $client = new Client();
        $response = $client->get($url);

        return $this->convertFromJsonRates(json_decode($response->getBody(),true));
    }

    protected function jsonRatesTimeSeries($from, $to, $dateStart, $dateEnd) {
        if ($this->settings['use-ssl']) {
            $url = 'https';
        }
        else {
            $url = 'http';
        }
        $url .= '://jsonrates.com/timeframe/?acces_key='.$this->settings['jsonrates-app-id'].'&source='.$from.'&currencies='.$to.'&start_date='.$dateStart.'&end_date='.$dateEnd;
        $this->requestUrl = $url;
        $client = new Client();
        $response = $client->get($url);

        return $this->convertFromJsonRatesSeries(json_decode($response->getBody(),true));
    }


    protected function yahoo() {

        $base = $this->base;

        if ($this->settings['use-ssl']) {
            $url = 'https';
        }
        else {
            $url = 'http';
        }

        $url .= '://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.xchange%20where%20pair%20in%20(%22';

        foreach ($this->settings['yahoo-currencies'] as $currency) {
            $url .= "$base$currency%2C";
        }
        $url .= '%22)&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys';

        $this->requestUrl = $url;

        $client = new Client();
        $response = $client->get($url);

        return $this->convertFromYahoo(json_decode($response->getBody(),true));
    }

    /*
     * Get the current rates.
     *
     * @param string $base the Currency base (will override config if set)
     * @param string $date for historical data.
     *
     * @return object returns a GuzzleHttp\Client object.
     */
    public function getRates($base = null, $date = null) {

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
            if (Cache::has("CConverter$api$base$date")) {
                $result = Cache::get("CConverter$api$base$date");
                $this->fromCache = true;
                if (Config::get('CConverter.enable-log')) {
                    Log::debug("Got currency rates from cache: CConverter$api$base$date");
                }
            }
            else {
                if ($api === 'yahoo') {
                    $result = $this->yahoo($base);
                }
                else if ($api === 'openexchange') {
                    $result = $this->openExchange($base);
                }

                else if ($api === 'jsonrates') {
                    $result = $this->jsonRates($base);
                }

                Cache::add("CConverter$api$base$date", $result, $this->settings['cache-min']);
                $this->fromCache = false;

                if (Config::get('CConverter.enable-log')) {
                    Log::debug('Added new currency rates to cache: CConverter'.$api.$base.$date.' - for '.$this->settings['cache-min'].' min.');
                }
            }
        }
        else {
            if ($api === 'yahoo') {
                $result = $this->yahoo($base);
            }
            else if ($api === 'openexchange') {
                $result = $this->openExchange($base);
            }
            else if ($api === 'jsonrates') {
                $result = $this->jsonRates($base);
            }

            $this->fromCache = false;
        }

        return $result;
    }

   /**
    * Get a RateSeries (not supported by Yahoo)
    *
    * @param type $from
    * @param type $to
    * @param type $dateStart
    * @param type $dateEnd
    *
    *  @return object returns a GuzzleHttp\Client object.
    */
    public function getRateSeries($from, $to, $dateStart, $dateEnd) {
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
                    return null;
                }
                else if ($api === 'openexchange') {
                    return null;
                    //$result = $this->openExchange($base);
                }

                else if ($api === 'jsonrates') {
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
                return null;
            }
            else if ($api === 'openexchange') {
                return null;
                //$result = $this->openExchange($base);
            }
            else if ($api === 'jsonrates') {
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
     * @param mixed $int calculate from this number
     * @param integer $round round this this number of desimals.
     * @param string $date date for historical data
     *
     * @return float $result
     */
    public function convert($from = null, $to, $int, $round = null, $date = null) {
        $result = array();

        if ($int === 0 or $int === null or $int === '') {
            return 0;
        }

        //A special case for openExchange free version.
        if (!$this->settings['openex-use-real-base'] and $this->settings['api-source'] === 'openexchange') {
            $base = 'USD';
        }

        else {
            $base = $from;
        }

        //Check if base currency is allready loaded in the model
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
                $result = $int * (float)$rates['rates'][$to];
            }

            //A special case for openExchange.
            else if ($to === 'USD' and !$this->settings['openex-use-real-base'] and $this->settings['api-source'] === 'openexchange') {
                $result = $int / (float)$rates['rates'][$from];
            }

            //When using openExchange free version we can still calculate other currencies trough USD.
            //Hope this math is right :)
            else if (!$this->settings['openex-use-real-base'] and $this->settings['api-source'] === 'openexchange'){
                $to_usd = (float)$rates['rates'][$to];
                $from_usd = (float)$rates['rates'][$from];
                $result =  $to_usd * ($int/$from_usd);
            }

            //Use actual base currency to calculate.
            else {
                $result = $int * (float)$rates['rates'][$to];
            }

            if ($round and $round > 0) {
                $result = round($result, $round);
            }
            else if ($round == 0) {
                $result = round($result);
            }
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
     *
     * @return float $result
     */
    public static function conv($from = null, $to, $int, $round = null, $date = null) {
        $convert = new self;
        return $convert->convert($from, $to, $int, $round, $date);
    }

    /*
     * Get the current rates.
     *
     * @param string $base the Currency base (will override config if set)
     * @param string $date for historical data.
     *
     * @return array
     */
    public static function rates($base = null, $date = null) {
        $rates = new self;
        return $rates->getRates($base, $date);
    }

    public function meta() {
        return ['settings' =>$this->settings,
                'url' => $this->requestUrl,
                'base' => $this->base,
                'fromCache' => $this->fromCache,
                'historicalDate' => $this->date];
    }


    protected function convertFromYahoo($data) {
        $base = $this->base;
        $output = array();
        $output['base'] = $base;
        $output['timestamp'] = strtotime($data['query']['created']);
        if (isset($data['query']['results']['rate']) and is_array($data['query']['results']['rate'])) {
            foreach ($data['query']['results']['rate'] as $row) {
                $key = str_replace("$base/", '', $row['Name']);
                $output['rates'][$key] = (float)$row['Ask'];
            }
            return $output;
        }
        else {
            Log::warning('No results returned from Yahoo.');
        }

    }

    protected function convertFromJsonRates($data) {
        $date = $this->date;
        $base = $data['source'];
        $output = array();
        $output['base'] = $base;

        if (isset($date)) {
            $output['timestamp'] = $data['rates'][$date]['timestamp'];
            if (isset($data['rates']) and is_array($data['rates'])) {
                foreach ($data['rates'][$date] as $key => $row) {
                    $key = str_replace($base, '', $key);
                    $output['rates'][$key] = (float)$row;
                }
            }
            else {
                Log::warning('No results returned from JsonRates.');
            }

        }
        else {
            $output['timestamp'] = $data['timestamp'];
            if (isset($data['rates']) and is_array($data['rates'])) {
                foreach ($data['rates'] as $key => $row) {
                    $key = str_replace($base, '', $key);
                    $output['rates'][$key] = (float)$row;
                }
            }
            else {
                Log::warning('No results returned from JsonRates.');
            }
        }

        return $output;
    }

    protected function convertFromJsonRatesSeries($data) {
        $base = $data['source'];
        $output = array();
        $output['base'] = $base;

        $output['to'] = $data['start_date'];
        $output['from'] = $data['end_date'];

        if (isset($data['rates']) and is_array($data['rates'])) {
            foreach ($data['rates'] as $key => $row) {
                $key = str_replace($base, '', $key);
                $output['rates'][$key]['timestamp'] = strtotime($row['utctime']);
                $output['rates'][$key]['rate'] = (float)$row['rate'];
            }
        }
        else {
            Log::warning('No results returned from JsonRates.');
        }

        return $output;
    }

    protected function convertFromOpenExchange($data) {
        $date = $this->date;
        $output = array();

        if (isset($date)) {
            if (isset($data['rates'][$date]) and is_array($data['rates'][$date])) {
                foreach ($data['rates'][$date] as $key => $row) {
                    $output['rates'][$key] = (float)$row;
                    $output['timestamp'] = strtotime($date);
                }
            }
            else {
                Log::warning('No results returned from OpenExchange.');
            }
        }
        else {
            if (isset($data['rates']) and is_array($data['rates'])) {
                $output['rates'] = $data['rates'];
                $output['timestamp'] = $data['timestamp'];
            }
            else {
                Log::warning('No results returned from OpenExchange.');
            }
        }

        $output['base'] = $data['base'];

        return $output;
    }
}
