<?php

namespace danielme85\CConverter\Providers;

/**
 * Class Rates Model
 *
 * @package danielme85\CConverter\Providers
 */
class Rates
{
    public $timestamp;
    public $date;
    public $base;
    public $extra;
    public $rates;
    public $url;
    public $error = false;

    /**
     * Convert rates to new currency
     *
     * @param string $currency
     *
     * @return Rates
     */
    public function convertBaseRateToCurrency(string $currency) : Rates
    {
        if (!empty($this->rates)) {
            if ($this->base !== $currency) {
                if (!empty($this->rates[$currency])) {
                    $usdrate = $this->rates[$currency];
                    foreach ($this->rates as $key => $rate) {
                        $newrates[$key] = $rate * (1 / $usdrate);
                    }
                }
            }
        }
        if (!empty($newrates)) {
            $this->rates = $newrates;
            $this->base = $currency;
        }

        return $this;
    }

    /**
     * Convert rates to USD
     *
     * @return Rates
     */
    public function convertBaseRatesToUSD() : Rates
    {
        if (!empty($this->rates)) {
            if ($this->base !== 'USD') {
                if (!empty($this->rates['USD'])) {
                    $usdrate = $this->rates['USD'];
                    foreach ($this->rates as $key => $rate) {
                        $newrates[$key] = $rate * (1 / $usdrate);
                    }
                }
            }
        }
        if (!empty($newrates)) {
            $this->rates = $newrates;
            $this->base = 'USD';
        }

        return $this;
    }

    public function toArray() : array
    {
        return json_decode(json_encode($this), true);
    }

}