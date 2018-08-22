<?php

namespace danielme85\CConverter\Providers;

/**
 * Class RateSeries Model
 *
 * @package danielme85\CConverter\Providers
 */
class RateSeries
{
    public $timestamp;
    public $dateStart;
    public $datetimeStart;
    public $base;
    public $extra;
    public $rates;

    public function toArray() : array
    {
        return json_decode(json_encode($this), true);
    }

}