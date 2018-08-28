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
     * @return Rates
     */
    public function rates(string $currency, string $date = null) : Rates
    {
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

        return $rates;
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
                $url .= "://data.fixer.io/api/$date?access_key=".$this->settings['fixer-access-key']."&base=$currency";
            } else {
                $url .= "://data.fixer.io/api/latest?access_key=".$this->settings['fixer-access-key']."&base=$currency";
            }
            $this->url = $url;
            $response = $this->connect($url);
        }

        return $response;
    }


    /**
     * Convert data from fixer.io to standardized format.
     *
     * @param string $input
     * @return Rates
     */
    private function convert($input) : Rates
    {
        $rates = new Rates();
        $rates->timestamp = time();
        $rates->date = $this->date;
        $rates->base = 'EUR';
        $rates->rates = [];
        $rates->url = $this->url;

        $data = json_decode($input, true);

        if (!empty($data)) {
            if (!empty($data['rates'])) {
                $rates->extra['fixer_date'] = $data['date'] ?? null;
                foreach ($data['rates'] as $key => $row) {
                    $rates->rates[$key] = $row;
                }
                //add 1:1 conversion rate from base for testing
                $rates->rates[$data['base']] = 1;
            }
        }
        else {
            $rates->error = "No data in response from Fixer.io";
        }

        return $rates;
    }
}