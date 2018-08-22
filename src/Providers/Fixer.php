<?php
/**
 * Created by Daniel Mellum <mellum@gmail.com>
 * Date: 8/18/2018
 * Time: 10:24 AM
 */

namespace danielme85\CConverter\Providers;

class Fixer extends BaseProvider implements ProviderInterface
{
    public $name = "Fixer.io";

    /**
     * Get Currency Rates
     *
     * @param string $currency
     * @param string|null $date
     *
     * @return array
     */
    public function rates(string $currency, string $date = null) : array
    {
        $results = [];
        $rates = $this->getBaseRates($currency, $date);
        if (empty($rates)) {
            if ($this->settings['fixer-use-real-base']) {
                $base = strtoupper($currency);
            }
            else {
                $base = 'EUR';
            }
            $rates = $this->convert($this->download($base, $date));
            if ($currency !== 'EUR') {
                //Set USD base rate
                $this->setBaseRates($rates->convertBaseRatesToUSD());

                if ($currency !== 'USD') {
                    $rates = $rates->convertBaseRateToCurrency($currency);
                    $this->setBaseRates($rates);
                }
            }
            else {
                $this->setBaseRates($rates);
            }
        }

        if (isset($rates->rates)) {
            $results = $rates->rates;
        }

        return $results;
    }


    /**
     *
     * @param string $currency
     * @param string|null $date
     *
     * @return array
     */
    public function download(string $currency, string $date = null) {
        //use test data if running as test
        if ($this->runastest) {
            $response = file_get_contents(dirname(__FILE__). '/../../tests/fixerTestData.json');
        }
        else {
            if ($this->settings['use-ssl']) {
                $url = 'https';
            } else {
                $url = 'http';
            }
            if (!empty($date)) {
                $url .= "://data.fixer.io/api/$date?base=$currency";
            } else {
                $url .= "://data.fixer.io/api/latest?base=$currency";
            }

            $response = $this->connect($url);
        }

        return $response;
    }


    /**
     * Convert data from fixer.io to standardized format.
     *
     * @param array $data
     * @return array
     */
    private function convert($input) {
        $data = json_decode($input, true);

        $time = strtotime($data['date']);

        $rates = new Rates();
        $rates->timestamp = time();
        $rates->date = date('Y-m-d', $time);
        $rates->datetime = date('Y-m-d H:i:s', $time);
        $rates->base = 'EUR';

        if (!empty($data)) {
            if (!empty($data['rates'])) {
                foreach ($data['rates'] as $key => $row) {
                    $newrates[$key] = $row;
                }
                //add 1:1 conversion rate from base for testing
                $newrates[$data['base']] = 1;
            }
        }
        $rates->rates = $newrates;

        return $rates;
    }
}