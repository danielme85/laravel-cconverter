<?php
/**
 * Created by Daniel Mellum <mellum@gmail.com>
 * Date: 8/18/2018
 * Time: 10:53 AM
 */

namespace danielme85\CConverter\Providers;

class jsonRates extends CurrencyProviders
{
    /**
     * Get data from jsonRates
     *
     * @return array
     */
    protected function jsonRates() {
        //use test data if running as test
        if ($this->runastest) {
            return $this->convertFromJsonRates(json_decode(file_get_contents(dirname(__FILE__). '/../tests/currencyLayerTestData.json'), true));
        }

        if ($this->settings['use-ssl']) {
            $url = 'https';
        }
        else {
            $url = 'http';
        }

        if (isset($date)) {
            $url .= '://apilayer.net/api/historical?access_key='.$this->settings['currencylayer-access-key'].'&date='.$this->date.'&source='.$this->base;
        }
        else {
            $url .= '://apilayer.net/api/live?access_key='.$this->settings['currencylayer-access-key'].'&source='.$this->base;
        }

        $this->requestUrl = $url;
        $client = new Client();
        $response = $client->get($url);

        return $this->convertFromJsonRates(json_decode($response->getBody(),true));
    }

    /**
     * Get jsonRates time series data
     *
     * @param string $from
     * @param string $to
     * @param string $dateStart
     * @param string $dateEnd
     * @return array
     */
    protected function jsonRatesTimeSeries($from, $to, $dateStart, $dateEnd) {
        if ($this->settings['use-ssl']) {
            $url = 'https';
        }
        else {
            $url = 'http';
        }
        $url .= '://apilayer.net/api/timeframe?access_key='.$this->settings['currencylayer-access-key'].'&source='.$from.'&currencies='.$to.'&start_date='.$dateStart.'&end_date='.$dateEnd;
        $this->requestUrl = $url;
        $client = new Client();
        $response = $client->get($url);

        return $this->convertFromJsonRatesSeries(json_decode($response->getBody(),true));
    }

    /**
     * Convert data from jsonRates to standardized format.
     *
     * @param array $data
     * @return array
     */
    protected function convertFromJsonRates($data) {
        if (!empty($data)) {
            if (isset($data['success'])) {
                if ($data['success']) {
                    $output = array();
                    $base = $data['source'];
                    $output['base'] = $base;
                    if ($this->date) {
                        $output['date'] = $this->date;
                    }
                    else {
                        $output['date'] = date('Y-m-d');
                    }
                    $output['timestamp'] = $data['timestamp'];
                    if (isset($data['quotes']) and is_array($data['quotes'])) {
                        foreach ($data['quotes'] as $key => $row) {
                            if ($key === "$base$base") {
                                $key = $base;
                            }
                            else {
                                $key = str_replace($base, '', $key);
                            }
                            $output['rates'][$key] = $row;
                        }
                    } else {
                        Log::warning('No results returned from CurrencyLayer.');
                    }

                    return $output;
                }
            }
        }
    }

    /**
     * Convert data from jsonRates Series to standardized format.
     *
     * @param array $data
     * @return array
     */
    protected function convertFromJsonRatesSeries($data) {
        echo json_encode($data);
        exit();
        $base = $data['source'];
        $output = array();
        $output['base'] = $base;

        $output['to'] = $data['start_date'];
        $output['from'] = $data['end_date'];

        if (isset($data['quotes']) and is_array($data['quotes'])) {
            foreach ($data['quotes'] as $key => $row) {
                $key = str_replace($base, '', $key);
                $output['rates'][$key]['timestamp'] = strtotime($row['utctime']);
                $output['rates'][$key]['rate'] = $row['quotes'];
            }
        }
        else {
            Log::warning('No results returned from CurrencyLayer.');
        }

        return $output;
    }
}