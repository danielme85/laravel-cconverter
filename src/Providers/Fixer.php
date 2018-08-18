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
     * Get data from fixer
     *
     * @return array
     */
    public function rates() {
        //use test data if running as test
        if ($this->runastest) {
            return $this->convert(json_decode(file_get_contents(dirname(__FILE__). '/../../tests/fixerTestData.json'), true));
        }

        //A special case for greedy fixer.io and their EUR base nonsense for free account.
        if (!$this->settings['openex-use-real-base'] and $this->settings['api-source'] === 'fixer') {
            $base = 'EUR';
        }
        else {
            $base = $this->base;
        }

        if ($this->settings['use-ssl']) {
            $url = 'https';
        }
        else {
            $url = 'http';
        }
        if ($this->date and $this->date != '') {
            $url .= "://data.fixer.io/$this->date?base=$base";
        }
        else {
            $url .= "://data.fixer.io/latest?base=$base";
        }


        return $this->convert(json_decode($response->getBody(),true));
    }


    /**
     * Convert data from fixer.io to standardized format.
     *
     * @param array $data
     * @return array
     */
    private function convert($data) {
        $output = array();
        if (!empty($data)) {
            if (!empty($data['rates'])) {
                $output['timestamp'] = time();
                $output['date'] = $data['date'];
                $this->date = $data['date'];

                foreach ($data['rates'] as $key => $row) {
                    $output['rates'][$key] = $row;
                }
                //add 1:1 conversion rate from base for testing
                $output['rates'][$data['base']] = 1;
            }
            else {
                Log::warning('No results returned from Fixer.io');
            }
        }
        else {
            if (isset($data['rates']) and is_array($data['rates'])) {
                $output['rates'] = $data['rates'];
                $output['timestamp'] = $data['timestamp'];
            }
            else {
                Log::warning('No results returned from Fixer.io');
            }
        }
        $output['base'] = $data['base'];

        return $output;
    }
}