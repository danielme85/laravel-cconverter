<?php
/**
 * Created by Daniel Mellum <mellum@gmail.com>
 * Date: 8/18/2018
 * Time: 10:57 AM
 */

namespace danielme85\CConverter\Providers;

use danielme85\CConverter\CurrencyProviders;

class Yahoo extends CurrencyProviders
{
    protected $yahooCurrencies = [
'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD',
'BND', 'BOB', 'BRL', 'BSD', 'BTC', 'BTN', 'BWP', 'BYR', 'BZD', 'CAD', 'CDF', 'CHF', 'CLF', 'CLP', 'CNY', 'COP', 'CRC',
'CUC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EEK', 'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL',
'GGP', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'IMP', 'INR', 'IQD',
'IRR', 'ISK', 'JEP', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP',
'LKR', 'LRD', 'LSL', 'LTL', 'LVL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MTL', 'MUR', 'MVR',
'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN',
'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'STD',
'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS',
'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XAG', 'XAU', 'XCD', 'XDR', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMK', 'ZMW', 'ZWL'
];

    /**
     * Get data form Yahoo
     *
     * @return array
     */
    protected function yahoo() {
        //use test data if running as test
        if ($this->runastest) {
            return $this->convertFromYahoo(json_decode(file_get_contents(dirname(__FILE__). '/../tests/yahooTestData.json'), true));
        }

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


    /**
     * Get Yahoo time series data
     *
     * @param string $from
     * @param string $to
     * @param string $dateStart
     * @param string $dateEnd
     * @return array
     */
    protected function yahooTimeSeries($from, $to, $dateStart, $dateEnd) {
        $this->base = 'USD';
        $this->from = $from;
        $this->to = $to;
        $this->fromDate = $dateStart;
        $this->toDate = $dateEnd;

        if ($this->settings['use-ssl']) {
            $url = 'https';
        }
        else {
            $url = 'http';
        }

        if ($to === 'USD') {
            $to = $from;
        }

        $url .= "://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.historicaldata%20where%20symbol%20%3D%20%22$to%3DX%22%20and%20startDate%20%3D%20%22$dateStart%22%20and%20endDate%20%3D%20%22$dateEnd%22&format=json&diagnostics=false&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&c";

        $this->requestUrl = $url;

        $client = new Client();
        $response = $client->get($url);

        return $this->convertFromYahooTimeSeries(json_decode($response->getBody(),true));
    }

    /**
     * @param array $data
     * @return array
     */

    protected function convertFromYahoo($data) {
        $base = $this->base;
        $output = array();
        $output['base'] = $base;
        $output['timestamp'] = strtotime($data['query']['created']);
        if (isset($data['query']['results']['rate']) and is_array($data['query']['results']['rate'])) {
            foreach ($data['query']['results']['rate'] as $row) {
                $key = str_replace("$base/", '', $row['Name']);
                $output['rates'][$key] = $row['Ask'];
            }
            return $output;
        }
        else {
            Log::warning('No results returned from Yahoo.');
        }

    }

    /**
     * Convert data from Yahoo Time Series to standardized format.
     *
     * @param array $data
     * @return array
     */
    protected function convertFromYahooTimeSeries($data) {
        $output = array();
        $output['base'] = $this->base;
        $output['from'] = $this->from;
        $output['to'] = $this->to;
        $output['fromDate'] =  $this->fromDate;
        $output['toDate'] =  $this->toDate;
        $output['timestamp'] = strtotime($data['query']['created']);

        if (isset($data['query']['results']['quote']) and is_array($data['query']['results']['quote'])) {
            foreach ($data['query']['results']['quote'] as $row) {
                $key = str_replace('%3dX', '', $row['Symbol']);
                $output['rates'][$key][$row['Date']] = $row['Adj_Close'];
            }
            //Yahoo historical data only supports USD as a base currency
            if ($this->from != 'USD') {
                $currencycompare = new self();
                $convertfrom = $currencycompare->getRateSeries('USD', $this->from, $this->fromDate, $this->toDate);
                if (!empty($convertfrom['rates'][$this->from]) and !empty($output['rates'][$this->to])) {
                    foreach($output['rates'][$this->to] as $date => $value) {
                        $tovalue = $convertfrom['rates'][$this->from][$date];
                        $output['rates'][$this->from][$date] = $tovalue;
                        $output['rates']["$this->from/$this->to"][$date] =  $value * (1/$tovalue);
                    }
                }
            }
            return $output;
        }
        else {
            Log::warning('No results returned from Yahoo.');
        }

    }
}