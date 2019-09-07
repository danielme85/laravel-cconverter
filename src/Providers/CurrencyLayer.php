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
     * @return Rates
     */
    public function rates(string $currency, string $date = null) : Rates
    {
        $rates = $this->getBaseRates($currency, $date);
        if (empty($rates)) {
            $rates = $this->convert($this->download($date));

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
     * Get data from jsonRates
     *
     * @return array
     */
    private function download($fromDate = null, $toDate = null) {
        //use test data if running as test
        if ($this->runastest) {
            $response = file_get_contents(dirname(__FILE__). '/../../tests/currencyLayerTestData.json');
        }
        else {
            $base = 'USD';

            if ($this->settings['use-ssl']) {
                $url = 'https';
            } else {
                $url = 'http';
            }

            if (!empty($fromDate) and !empty($toDate)) {
                $url .= '://apilayer.net/api/timeframe?access_key='.$this->settings['currencylayer-access-key'].'&source='.$base.'&start_date='.$fromDate.'&end_date='.$toDate;
                }
            else if (!empty($fromDate)) {
                $url .= '://apilayer.net/api/historical?access_key=' . $this->settings['currencylayer-access-key'] . '&date=' . $fromDate . '&source=' . $base;

            } else {
                $url .= '://apilayer.net/api/live?access_key=' . $this->settings['currencylayer-access-key'] . '&source=' . $base;
            }
            $this->url = $url;
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
        $rates = new Rates();
        $rates->timestamp = time();
        $rates->date = $this->date;
        $rates->base = 'USD';
        $rates->rates = [];
        $this->url = $this->url;

        $data = json_decode($input, true);

        if (!empty($data)) {
            if (isset($data['success'])) {
                if ($data['success']) {
                    $rates->extra['cl_timestamp'] = $data['timestamp'] ?? null;
                    $newrates = [];
                    if (isset($data['quotes']) and is_array($data['quotes'])) {
                        foreach ($data['quotes'] as $key => $row) {
                            if ($key === "$rates->base$rates->base") {
                                $key = $rates->base;
                            } else {
                                $key = str_replace($rates->base, '', $key);
                            }
                            $newrates[$key] = round($row, 5);
                        }
                    }
                }
            }
            if (isset($data['error'])) {
                $rates->extra['cl_error'] = $data['error'] ?? null;
            }
        }
        else {
            $rates->error = "No data in response from Currency Layer.";
        }

        if (!empty($newrates)) {
            $rates->rates = $newrates;
        }

        return $rates;
    }
}