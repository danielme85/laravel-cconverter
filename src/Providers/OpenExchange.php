<?php
/**
 * Created by Daniel Mellum <mellum@gmail.com>
 * Date: 8/18/2018
 * Time: 10:24 AM
 */

namespace danielme85\CConverter\Providers;

use Illuminate\Support\Facades\Log;

class OpenExchange extends BaseProvider implements ProviderInterface
{
    public $name = 'Open Exchange Rates';

    /**
     * Get the rates from this provider.
     *
     * @param string $currency
     * @param string $date
     *
     * @return Rates
     */
    public function rates(string $currency, string $date): Rates
    {
        $rates = $this->getBaseRates($currency, $date);
        if (empty($rates)) {
            $rates = $this->convert($this->download($currency, $date));
            if ($currency !== 'EUR') {
                //Set USD base rate
                $this->setBaseRates($rates->convertBaseRatesToUSD());

                if ($currency !== 'USD') {
                    $rates = $rates->convertBaseRateToCurrency($currency);
                    $this->setBaseRates($rates);
                }
            } else {
                $this->setBaseRates($rates);
            }
        }

        return $rates;
    }

    /**
     * Get data from openExchange
     *
     * @return array
     */
    private function download($currency, $date)
    {
        //use test data if running as test
        if ($this->runastest) {
            $results = file_get_contents(dirname(__FILE__) . '/../../tests/openExchangeTestData.json');
        }
        else {
            //A special case for openExchange free version.
            if (!$this->settings['openex-use-real-base']) {
                $base = 'USD';
            } else {
                $base = $currency;
            }

            if ($this->settings['use-ssl']) {
                $url = 'https';
            } else {
                $url = 'http';
            }

            if ($date === date('Y-m-d')) {
                $url .= '://openexchangerates.org/api/latest.json?app_id=' . $this->settings['openex-app-id'] . '&base=' . $base;
            } else {
                $url .= '://openexchangerates.org/api/time-series.json?app_id=' . $this->settings['openex-app-id'] . '&start=' . $date . '&end=' . $date . '&base=' . $base;
            }

            $results = $this->connect($url);
        }

        return $results;
    }

    /**
     * Convert data from from OpenExchangeRate to standardized format.
     * @param $data
     *
     * @return Rates
     */
    protected function convert($input) : Rates
    {
        $rates = new Rates();
        $rates->timestamp = time();
        $rates->date = $this->date;
        $rates->base = 'USD';
        $rates->rates = [];

        if ($this->date !== date('Y-m-d')) {
            $date = $this->date;
        } else {
            $date = null;
        }

        $data = json_decode($input, true);
        if (!empty($data)) {
            if (!empty($date)) {
                if (isset($data['rates'][$date]) and is_array($data['rates'][$date])) {
                    foreach ($data['rates'][$date] as $key => $row) {
                        $rates->rates[$key] = $row;
                    }
                } else {
                    Log::warning('No results returned from OpenExchange.');
                }
            } else {
                if (isset($data['rates']) and is_array($data['rates'])) {
                    $rates->rates = $data['rates'];
                } else {
                    Log::warning('No results returned from OpenExchange.');
                }
            }
        }

        return $rates;
    }

}