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
    public $base;
    public $date;
    public $fromDate;
    public $toDate;
    public $from;
    public $to;
    public $baseRates;
    public $api;
    public $logEnabled = false;

    protected $runastest = false;

    public function __construct($settings)
    {
        $this->api = $settings['api-source'];
        $this->logEnabled = $settings['enable-log'];
        $this->runastest = $settings['runastest'];
    }

    protected function connect($url)
    {
        $client = new Client();
        $request = $client->get($url);
        $response = $request->getBody();
        return $response;
    }

    protected function convertBaseRatesToCurrency($currency) {
        if (!empty($this->baseRates)) {
            $rates = $this->baseRates;

            if ($rates['base'] !== $currency) {
                if (!empty($rates['rates']) and !empty($rates['rates'][$currency])) {
                    $usdrate = $rates['rates'][$currency];
                    $rateseries = $rates['rates'];
                    foreach ($rateseries as $key => $rate) {
                        $newrates[$key] = $rate * (1 / $usdrate);
                    }
                }
            }
        }
        if (!empty($newrates)) {
            $rates['rates'] = $newrates;
            $rates['base'] = $currency;
        }

        return $rates;
    }

    protected function convertBaseRatesToUSD() {
        if (!empty($this->baseRates)) {
            $rates = $this->baseRates;
            if ($rates['base'] !== 'USD') {
                if (!empty($rates['rates']) and !empty($rates['rates']['USD'])) {
                    $usdrate = $rates['rates']['USD'];
                    $rateseries = $rates['rates'];
                    foreach ($rateseries as $key => $rate) {
                        $newrates[$key] = $rate * (1 / $usdrate);
                    }
                }
            }
        }
        if (!empty($newrates)) {
            $rates['rates'] = $newrates;
            $rates['base'] = 'USD';
        }

        $this->baseRates = $rates;
    }

}