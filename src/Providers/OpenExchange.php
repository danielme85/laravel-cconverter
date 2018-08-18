<?php
/**
 * Created by Daniel Mellum <mellum@gmail.com>
 * Date: 8/18/2018
 * Time: 10:24 AM
 */

namespace danielme85\CConverter\Providers;

use danielme85\CConverter\CurrencyProviders;

class OpenExchange extends CurrencyProviders
{

    /**
     * Get data from openExchange
     *
     * @return array
     */
    protected function connect() {
        //use test data if running as test
        if ($this->runastest) {
            return $this->convertFromOpenExchange(json_decode(file_get_contents(dirname(__FILE__). '/../tests/openExchangeTestData.json'), true));
        }

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

        return $this->convert(json_decode($response->getBody(),true));
    }

    /**
     * Convert data from from OpenExchangeRate to standardized format.
     * @param $data
     * @return array
     */
    protected function convert($data) {
        $date = $this->date;
        $output = array();

        if (isset($date)) {
            if (isset($data['rates'][$date]) and is_array($data['rates'][$date])) {
                foreach ($data['rates'][$date] as $key => $row) {
                    $output['rates'][$key] = $row;
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