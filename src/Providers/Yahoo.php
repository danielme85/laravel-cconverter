<?php
/**
 * Created by Daniel Mellum <mellum@gmail.com>
 * Date: 8/18/2018
 * Time: 10:57 AM
 */

namespace danielme85\CConverter\Providers;

class Yahoo extends BaseProvider implements ProviderInterface
{
    private $yahooCurrencies = [
    'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BDT', 'BHD', 'BIF', 'BND',
    'BOB', 'BRL', 'BSD', 'BTC', 'BTN', 'BWP', 'BYR', 'BZD', 'CAD', 'CDF', 'CHF', 'CLF', 'CLP', 'CNY', 'COP', 'CRC',
    'CUC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EEK', 'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL',
    'GGP', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'IMP', 'INR', 'IQD',
    'IRR', 'ISK', 'JEP', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KZT', 'LAK', 'LBP',
    'LKR', 'LRD', 'LSL', 'LTL', 'LVL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MTL', 'MUR', 'MVR',
    'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN',
    'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'STD',
    'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VEF',
    'VND', 'VUV', 'WST', 'XAF', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMK', 'ZMW', 'ZWL'
];

    /**
     * Get the rates from this provider.
     *
     * @param string $currency
     * @param string $date
     *
     * @return Rates
     */
    public function rates(string $currency, string $date) : Rates
    {
        //Yahoo only supports rates "right now"
        $date = date('Y-m-d');

        $rates = $this->getBaseRates($currency, $date);
        if (empty($rates)) {
            $rates = $this->convert($this->download());

            if ($currency !== 'USD') {
                $rates = $rates->convertBaseRateToCurrency($currency);
                $this->setBaseRates($rates);
            }
            else {
                $this->setBaseRates($rates);
            }
        }

        return $rates;
    }

    /**
     * Get data form Yahoo
     *
     * @return array
     */
    private function download() {
        //use test data if running as test
        if ($this->runastest) {
            $response = file_get_contents(dirname(__FILE__). '/../../tests/yahooTestData.json');
        }
        else {
            $base = 'USD';
            if ($this->settings['use-ssl']) {
                $url = 'https';
            } else {
                $url = 'http';
            }

            $url .= '://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.xchange%20where%20pair%20in%20(%22';

            foreach ($this->yahooCurrencies as $currency) {
                $url .= "$base$currency%2C";
            }
            $url .= '%22)&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys';
            $this->url = $url;
            $response = $this->connect($url);
        }

        return $response;
    }



    protected function convert($input) {
        $rates = new Rates();
        $rates->timestamp = time();
        $rates->date = $this->date;
        $rates->base = 'USD';
        $rates->rates = [];
        $rates->url = $this->url;

        if (!empty($data)) {
            $data = json_decode($input, true);
            if (!empty($data)) {
                if (!empty($data['query'])) {

                    $rates->extra['query_created'] = $data['query']['created'] ?? null;

                    if (isset($data['query']['results']['rate']) and is_array($data['query']['results']['rate'])) {
                        foreach ($data['query']['results']['rate'] as $row) {
                            $key = str_replace("$rates->base/", '', $row['Name']);
                            if ($key !== 'N/A') {
                                $value = $row['Ask'];
                                if ($value === 'N/A') {
                                    $value = 0;
                                } else {
                                    $value = floatval($value);
                                }
                                $newrates[$key] = $value;
                            }
                        }
                    }
                    if (!empty($newrates)) {
                        $rates->rates = $newrates;
                    }
                }
                else {
                    $rates->error = "No results returned from Yahoo Financial data.";
                }
            }
        }
        else {
            $rates->error = "No data in response from Yahoo Financial data.";
        }

        return $rates;
    }
}