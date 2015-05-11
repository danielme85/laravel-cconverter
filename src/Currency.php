<?php namespace danielme85\CConverter;
/* 
 * The MIT License
 *
 * Copyright 2015 Daniel Mellum.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


/*
 * 
 * @param boolean $useCache Disable/Enable cache (will override config if set)
 */
class Currency {
    
    private $https, $key, $url, $cache, $timestamp, $requestUrl;
    
    
    public function __construct($https = null, $useCache = null) {
        
        $this->key = Config::get('CConverter.cc-app-id');
        $this->url = '://openexchangerates.org/api/latest.json';
        
        if ($https) {
            $this->https = $https;
        }
        else {
           $this->https = Config::get('CConverter.cc-use-https'); 
        }
        
        if ($useCache) {
            $this->cache = $useCache;
        }
        else {
          $this->cache = Config::get('CConverter.cc-enable-cache');  
        }
    }
    
    /*
     * Get the current rates.
     * 
     * @param string $base the Currency base (will override config if set)
     * 
     * 
     * @return object returns a GuzzleHttp\Client object. 
     */
    public function getRates($base = null) {
        if ($this->https) {
            $baseUrl = 'https';
        }
        else {
            $baseUrl = 'http';
        }
        
        if ($base) {
            $baseCurrency = $base;
        }
        else {
            $baseCurrency = Config::get('CConverter.cc-base-currency');
        }
        
        $url = $baseUrl . $this->url . '?app_id=' . $this->key .'&base='.$baseCurrency;      
        $this->requestUrl = $url;
        
        if ($this->cache) {
            if (Cache::get('currencyRates')) {
                $result = Cache::get('currencyRates');              
                if (Config::get('CConverter.cc-enable-log')) {
                    Log::debug('Got currency rates from cache.');
                }
            }
            else {
                $client = new Client();
                $response = $client->get($url);
                Cache::add('currencyRates', $response->json(), 60);
                if (Config::get('CConverter.cc-enable-log')) {
                    Log::debug('Added new currency rates to cache.');
                }
            }
            $result = $response->json();             
        }    
        
        else {
            $client = new Client();
            $response = $client->get($url);           
            $result = $response->json();
            
        }
        
        $this->timestamp = $result['timestamp'];
        return $result;
                    
    }
    
    /*
     * Convert a from one currecnty to antoher
     * 
     * @param string $from ISO4217 country code
     * @param string $to ISO4217 country code 
     * @param mixed $int calculate from this number
     * @param integer $round round this this number of desimals.
     * 
     * @return float $result
     */
    public function convert($from = null, $to, $int, $round = null) {
        $rates = $this->getRates($from);
       
        $result = $int * (float)$rates['rates'][$to];   
        
        if ($round) {
            $result = round($result, $round);
        }
        
        return $result;
               
    }
    
    public function meta() {
        $meta = ['cache' => $this->cache, 'datestamp' => $this->timestamp, 'url' => $this->requestUrl];
    }

}

