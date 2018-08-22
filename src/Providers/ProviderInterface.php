<?php
/**
 * Created by Daniel Mellum <mellum@gmail.com>
 * Date: 8/18/2018
 * Time: 11:33 AM
 */

namespace danielme85\CConverter\Providers;


interface ProviderInterface
{
    public function rates(string $currency, string $date);
}