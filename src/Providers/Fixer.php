<?php
/**
 * Created by Daniel Mellum <mellum@gmail.com>
 * Date: 8/18/2018
 * Time: 10:24 AM
 */

namespace danielme85\CConverter\Providers;

class Fixer extends BaseProvider implements ProviderInterface
{
    /**
     *
     * @param string $currency
     * @param string|null $date
     *
     * @return array
     */
    public function rates(string $currency, string $date = null) {
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
                $url .= "://data.fixer.io/api/$date?base=$this->baseCurrency";
            } else {
                $url .= "://data.fixer.io/api/latest?base=$this->baseCurrency";
            }

            $response = $this->connect($url);
        }


        $rates = $this->convert($response);
        $this->rates = $rates;

        return $this->rates;
    }


    /**
     * Convert data from fixer.io to standardized format.
     *
     * @param array $data
     * @return array
     */
    private function convert($input) {
        $data = json_decode($input, true);

        $output['timestamp'] = time();
        $output['date'] = date('Y-m-d');
        $output['datetime'] = date('Y-m-d H:i:s');
        $output['base'] = $data['base'];
        $output['extra'] = [];
        $output['rates'] = [];

        if (!empty($data)) {
            if (!empty($data['rates'])) {
                foreach ($data['rates'] as $key => $row) {
                    $newrates[$key] = $row;
                }
                //add 1:1 conversion rate from base for testing
                $newrates[$data['base']] = 1;
            }
        }
        $output['rates'] = $newrates;


        return $this->convertBaseRatesToUSD($output);
    }
}