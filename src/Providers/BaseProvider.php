<?php
/**
 * Created by Daniel Mellum <mellum@gmail.com>
 * Date: 8/18/2018
 * Time: 11:24 AM
 */

namespace danielme85\CConverter\Providers;

use GuzzleHttp\Client;

class BaseProvider
{
    public $base;
    public $date;
    public $fromDate;
    public $toDate;
    public $from;
    public $to;

    protected $settings = [];

    protected $runastest = false;

    public function setTestMode(bool $runastest)
    {
        $this->runastest = $runastest;
    }

    public function addSettings(array $settings)
    {
        $this->settings = $settings;
    }

    function connect($url)
    {
        $client = new Client();
        return $client->get($url);
    }

}