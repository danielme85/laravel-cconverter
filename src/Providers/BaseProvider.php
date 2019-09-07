<?php
/**
 * Created by Daniel Mellum <mellum@gmail.com>
 * Date: 8/18/2018
 * Time: 11:24 AM
 */

namespace danielme85\CConverter\Providers;

use GuzzleHttp\Client;

class BaseProvider
{
    public $api;
    public $logEnabled;

    protected $date;
    protected $currency;
    protected $baseRates;

    protected $runastest;
    protected $settings;

    protected $url;

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
     * @return string
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
        $response = $request->getBody()->getContents();

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
     * @param string $date
     *
     * @return Rates|null
     */
    protected function getBaseRates(string $currency, string $date)
    {
        $this->currency = $currency;
        $this->date = $date;

        if (isset($this->baseRates[strtoupper($currency)][$date])) {

            return $this->baseRates[strtoupper($currency)][$date];
        }

        return null;
    }

}