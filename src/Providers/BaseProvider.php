<?php
/**
 * Created by Daniel Mellum <mellum@gmail.com>
 * Date: 8/18/2018
 * Time: 11:24 AM
 */

namespace danielme85\CConverter\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class BaseProvider
{
    public $api;
    public $logEnabled;

    protected $baseRates;
    protected $baseRateSeries;

    protected $runastest;
    protected $settings;

    /**
     * BaseProvider constructor.
     *
     * @param array $settings
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->api = $settings['api-source'];
        $this->logEnabled = $settings['enable-log'];
        $this->runastest = $settings['runastest'];
    }

    /**
     * Connect to the providers API
     *
     * @param string $url
     * @param array $headers
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function connect($url, $headers = null)
    {
        if (!empty($headers)) {
            $client = new Client(['headers' => $headers]);

        }
        else {
            $client = new Client();

        }
        $request = $client->get($url);
        $response = $request->getBody();

        return $response;
    }

    /**
     * Set rates
     *
     * @param Rates $rates
     *
     * @return bool
     */
    protected function setBaseRates(Rates $rates) : bool
    {
        if (!empty($rates)) {
            if (isset($rates->base) and isset($rates->date)) {
                $this->baseRates[$rates->base][$rates->date] = $rates;

                if (!empty($this->baseRates[$rates->base][$rates->date])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get rates
     *
     * @param string $currency
     * @param $date
     *
     * @return Rates|null
     */
    protected function getBaseRates(string $currency, $date)
    {
        if (isset($this->baseRates[strtoupper($currency)][$date])) {

            return $this->baseRates[strtoupper($currency)][$date];
        }

        return null;
    }

    /**
     * Get rate series
     *
     * @param string $currency
     * @param $date
     *
     * @return Rates|null
     */
    protected function getBaseRateSeries(string $currency)
    {
        if (isset($this->baseRates[strtoupper($currency)])) {

            return $this->baseRates[strtoupper($currency)];
        }

        return null;
    }

}