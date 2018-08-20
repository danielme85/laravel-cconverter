<?php


namespace danielme85\CConverter\Providers;


class EuropeanCentralBank extends BaseProvider
{

    public function rate(string $currency, string $date = null) {
        $currency = strtoupper($currency);
        if (empty($this->baseRates) or $this->baseRates['date'] !== $date) {
            $this->download($date);
        }

        //Rates are stored in USD
        if ($currency === 'USD') {
            if (isset($this->baseRates['rates'][$currency])) {
                return $this->baseRates['rates'][$currency];
            }
        }
        else {
            $rate = $this->baseRates['rates'][$currency];
            $usdrate = $this->baseRates['rates'][$currency];

            return $rate * (1 / $usdrate);
        }

        return 0;
    }

    public function rates(string $currency, string $date = null) {
        if (empty($this->baseRates) or $this->baseRates['date'] !== $date) {
           $this->download($date);
        }

        //Rates are stored in USD
        if ($currency === 'USD') {
            return $this->baseRates;
        }
        else {
            return $this->convertBaseRatesToCurrency($currency);
        }
    }


    private function download($fromdate = null, $todate = null) {
        d("DOWNLOAD CALLED");
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
            if (!empty($fromdate)) {
                $fromdate = date('Y-m-d');
            }
            if (!empty($todate)) {
                $todate = date('Y-m-d');
            }
            $url .= "://sdw-wsrest.ecb.europa.eu/service/data/EXR/D..EUR.SP00.A?startPeriod=$fromdate&endPeriod=$todate&detail=dataonly";

            $response = $this->connect($url);
        }

        $baseRates = $this->convert($response);
        $this->baseRates = $baseRates;
        $this->convertBaseRatesToUSD();
    }

    /**
     * Convert data from fixer.io to standardized format.
     *
     */
    private function convert($input) {
        $data = json_decode($input, true);
        $series = end($data['dataSets']);
        $structure = $data['structure']['dimensions']['series'];

        $output['timestamp'] = time();
        $output['date'] = date('Y-m-d');
        $output['datetime'] = date('Y-m-d H:i:s');
        $output['base'] = 'EUR';
        $output['extra'] = ['european_central_bank_valid_from' => $series['validFrom']];
        $output['rates'] = [];

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
                $output['rates'][$label] = $newrates[$i];
            }
            //add 1:1 conversion rate from base for testing
            $output['rates']['EUR'] = 1;
        }

        return $output;
    }
}