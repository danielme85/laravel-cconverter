<?php
/**
 * Created by PhpStorm.
 * User: danielme85
 * Date: 8/21/17
 * Time: 9:21 PM
*/

use danielme85\CConverter\Currency;

class CurrencyConvertTest extends Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return ['danielme85\CConverter\CConverterServiceProvider'];
    }

    /**
     * @group experiment
     */
    public function testExperiment() {
        $currency = new Currency('fixer', null, null, null, true);
        d($currency->getRates('USD'));

    }

    /**
     * Test of default settings and init
     *
     * @group general
     *
     * @return void
     */
    public function testDefaultApi() {
        $this->assertEquals(1 , Currency::conv('USD', 'USD', 1));
        $this->assertNotEmpty(Currency::rates());
        $this->assertNotEmpty(Currency::rates('USD', date('Y-m-d')));
    }

    /**
     * Test to see if the Currency object can be created with fixer.
     * @group fixer
     *
     * @return void
     */
    public function testCreateInstanceFixer()
    {
        $currency = new Currency('fixer', null, null, null, true);
        $this->assertNotEmpty($currency);
    }

    /**
     * Test Currency conversion default config with fixer.io as source.
     * @group fixer
     *
     * @return void
     */
    public function testConvertFixer() {
        $currency = new Currency('fixer', null, null, null, true);
        $this->assertEquals(1, $currency->convert('USD', 'USD', 1));
    }


    /**
     * Test getting Currency rates with fixer.io as source.
     * @group fixer
     *
     * @return void
     */
    public function testGetRatesFixer() {
        $currency = new Currency('fixer', null, null, null, true);
        $rates = $currency->getRates('USD');
        $this->assertArrayHasKey('rates', $rates);
        $this->assertGreaterThan(0, $this->count($rates['rates']));
    }

    /**
     * Test to see if the Currency object can be created with CurrencyLayer.
     * @group currencylayer
     *
     * @return void
     */
    public function testCreateInstanceCurrencyLayer()
    {
        $currency = new Currency('currencylayer', null, null, null, true);
        $this->assertNotEmpty($currency);
    }

    /**
     * Test Currency conversion default config with CurrencyLayer as source.
     * @group currencylayer
     *
     * @return void
     */
    public function testConvertCurrencyLayer() {
        $currency = new Currency('currencylayer', null, null, null, true);
        $this->assertEquals(1, $currency->convert('USD', 'USD', 1));
    }

    /**
     * Test getting Currency rates with CurrencyLayer as source.
     * @group currencylayer
     *
     * @return void
     */
    public function testGetRatesCurrencyLayer() {
        $currency = new Currency('currencylayer', null, null, null, true);
        $rates = $currency->getRates('USD');
        $this->assertArrayHasKey('rates', $rates);
        $this->assertGreaterThan(0, $this->count($rates['rates']));
    }

    /**
     * Test to see if the Currency object can be created with Yahoo Finance.
     * @group yahoo
     *
     * @return void
     */
    public function testCreateInstanceYahoo()
    {
        $currency = new Currency('yahoo', null, null, null, true);
        $this->assertNotEmpty($currency);
    }

    /**
     * Test Currency conversion default config with Yahoo Finance as source.
     * @group yahoo
     *
     * @return void
     */
    public function testConvertYahoo() {
        $currency = new Currency('yahoo', null, null, null, true);
        $this->assertEquals(1, $currency->convert('USD', 'USD', 1));
    }

    /**
     * Test getting Currency rates with Yahoo Finance as source.
     * @group yahoo
     *
     * @return void
     */
    public function testGetRatesYahoo() {
        $currency = new Currency('yahoo', null, null, null, true);
        $rates = $currency->getRates('USD');
        $this->assertArrayHasKey('rates', $rates);
        $this->assertGreaterThan(0, $this->count($rates['rates']));
    }

    /**
     * Test to see if the Currency object can be created with OpenExchange.
     * @group openexchange
     *
     * @return void
     */
    public function testCreateInstanceOpenExchange()
    {
        $currency = new Currency('openexchange', null, null, null, true);
        $this->assertNotEmpty($currency);
    }

    /**
     * Test Currency conversion default config with OpenExchange as source.
     * @group openexchange
     *
     * @return void
     */
    public function testConvertOpenExchange() {
        $currency = new Currency('openexchange', null, null, null, true);
        $this->assertEquals(1, $currency->convert('USD', 'USD', 1));
    }

    /**
     * Test getting Currency rates with OpenExchange as source.
     * @group openexchange
     *
     * @return void
     */
    public function testGetRatesOpenExchange() {
        $currency = new Currency('openexchange', null, null, null, true);
        $rates = $currency->getRates('USD');
        $this->assertArrayHasKey('rates', $rates);
        $this->assertGreaterThan(0, $this->count($rates['rates']));
    }

}