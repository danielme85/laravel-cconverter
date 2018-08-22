<?php
/**
 * Created by Daniel Mellum <mellum@gmail.com>
 * Date: 8/18/2018
 * Time: 10:53 AM
 */

namespace danielme85\CConverter\Providers;

class CurrencyLayer extends BaseProvider implements ProviderInterface
{

    /**
     * Get Currency Rates
     *
     * @param string $currency
     * @param string|null $date
     *
     * @return array
     */
    public function rates(string $currency, string $date = null) {
        $results = [];
        $rates = $this->getBaseRates($currency, $date);
        if (empty($rates)) {
            $rates = $this->convert($this->download($currency, $date));
            $this->setBaseRates($rates);
        }

        if (isset($rates->rates)) {
            $results = $rates->rates;
        }

        return $results;
    }

    /**
     * Get data from jsonRates
     *
     * @return array
     */
    private function download($currency, $fromDate = null, $toDate = null) {
        //use test data if running as test
        if ($this->runastest) {
            $response = file_get_contents(dirname(__FILE__). '/../../tests/currencyLayerTestData.json');
        }
        else {
            if ($this->settings['use-ssl']) {
                $url = 'https';
            } else {
                $url = 'http';
            }

            if (!empty($fromDate) and !empty($toDate)) {
                $url .= '://apilayer.net/api/timeframe?access_key='.$this->settings['currencylayer-access-key'].'&source='.$currency.'&start_date='.$fromDate.'&end_date='.$toDate;
                }
            else if (!empty($fromDate)) {
                $url .= '://apilayer.net/api/historical?access_key=' . $this->settings['currencylayer-access-key'] . '&date=' . $fromDate . '&source=' . $currency;

            } else {
                $url .= '://apilayer.net/api/live?access_key=' . $this->settings['currencylayer-access-key'] . '&source=' . $currency;
            }

            $response = $this->connect($url);
        }

        return $response;
    }


    /**
     * Convert data from jsonRates to standardized format.
     *
     * @param array $data
     * @return Rates
     */
    private function convert($input): Rates
    {
        $data = json_decode($input, true);
        if (!empty($data)) {

            $time = $data['timestamp'];

            $rates = new Rates();
            $rates->timestamp = time();
            $rates->date = date('Y-m-d', $time);
            $rates->datetime = date('Y-m-d H:i:s', $time);
            $rates->base = strtoupper($data['source']);
            $rates->extra = [];
            $rates->rates = [];
            $newrates = [];

            if (isset($data['success'])) {
                if ($data['success']) {
                    if (isset($data['quotes']) and is_array($data['quotes'])) {
                        foreach ($data['quotes'] as $key => $row) {
                            if ($key === "$rates->base$rates->base") {
                                $key = $rates->base;
                            } else {
                                $key = str_replace($rates->base, '', $key);
                            }
                            $newrates[$key] = $row;
                        }
                    }
                }
            }
        }
        if (!empty($newrates)) {
            $rates->rates = $newrates;
        }

        return $rates;
    }

    /**
     * Convert data from jsonRates Series to standardized format.
     *
     * @param array $data
     * @return array
     */
    private function convertFromJsonRatesSeries($data) {
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