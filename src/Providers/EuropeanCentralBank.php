<?php


namespace danielme85\CConverter\Providers;

/**
 * Class EuropeanCentralBank
 *
 * @package danielme85\CConverter\Providers
 */
class EuropeanCentralBank extends BaseProvider implements ProviderInterface
{

    public $name = 'The European Central Bank';

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
        $rates = $this->getBaseRates($currency, $date);
        if (empty($rates)) {
            $rates = $this->convert($this->download($date));
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
     * Download new data
     *
     * @param null|string $date
     *
     * @return mixed
     */
    private function download($date = null)
    {
        //use test data if running as test
        if ($this->runastest) {
            $response = file_get_contents(dirname(__FILE__). '/../../tests/europeanCentralBankTestData.json');
        }
        else {
            if ($this->settings['use-ssl']) {
                $url = 'https';
            } else {
                $url = 'http';
            }
            if (!empty($date)) {
                $date = date('Y-m-d');
            }
            $url .= "://sdw-wsrest.ecb.europa.eu/service/data/EXR/D..EUR.SP00.A?startPeriod=$date&endPeriod=$date&detail=dataonly";

            $response = $this->connect($url, [
                'Accept' => 'application/vnd.sdmx.data+json;version=1.0.0-wd',
                'Accept-Encoding' => 'gzip'
            ]);
        }

        return $response;
    }


    /**
     * Convert data from fixer.io to standardized format.
     * @param $input
     *
     * @return Rates
     */
    private function convert($input) : Rates
    {
        $data = json_decode($input, true);
        $series = end($data['dataSets']);
        $structure = $data['structure']['dimensions']['series'];

        $time = strtotime($series['validFrom']);

        $rates = new Rates();
        $rates->timestamp = time();
        $rates->date = date('Y-m-d', $time);
        $rates->datetime = date('Y-m-d H:i:s', $time);
        $rates->base = 'EUR';
        $rates->extra = ['european_central_bank_valid_from' => $series['validFrom']];
        $rates->rates = [];

        $newrates = [];

        if (!empty($structure)) {
            foreach ($structure as $struc) {
                if ($struc['id'] === 'CURRENCY') {
                    if (!empty($struc['values'])) {
                        foreach ($struc['values'] as $label) {
                            $labels[] = $label['id'];
                        }
                    }
                }
            }
        }

        if (!empty($series['series'])) {
            foreach ($series['series'] as $row) {
                $avg = 0;
                $counter = 0;
                foreach ($row as $subrow) {
                    if (!empty($subrow)) {
                        foreach ($subrow as $value) {
                            $avg += $value[0];
                            $counter++;
                        }
                    }
                }
                $newrates[] = $avg/$counter;
            }
        }

        if (!empty($labels) and !empty($newrates)) {
            foreach ($labels as $i => $label) {
                $rates->rates[$label] = $newrates[$i];
            }
            //add 1:1 conversion rate from base for testing
            $rates->rates['EUR'] = 1;
        }

        return $rates;
    }
}