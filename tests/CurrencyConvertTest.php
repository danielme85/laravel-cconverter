<?php
/**
 * Created by PhpStorm.
 * User: danielme85
 * Date: 8/21/17
 * Time: 9:21 PM
*/


class CurrencyConvertTest extends Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return ['danielme85\CConverter\CConverterServiceProvider'];
    }

    /**
     * Test to see if the Currency object can be created with fixer.
     * @group fixer
     *
     * @return void
     */
    public function testCreateInstanceFixer()
    {
        $curreny = new \danielme85\CConverter\Currency('fixer', null, null, null, true);
        $this->assertNotEmpty($curreny);
    }

    /**
     * Test Currency conversion default config with fixer.io as source.
     * @group fixer
     *
     * @return void
     */
    public function testConvertFixer() {
        $curreny = new \danielme85\CConverter\Currency('fixer', null, null, null, true);
        $this->assertEquals(1, $curreny->convert('USD', 'USD', 1));
    }


    /**
     * Test getting Currency rates with fixer.io as source.
     * @group fixer
     *
     * @return void
     */
    public function testGetRatesFixer() {
        $curreny = new \danielme85\CConverter\Currency('fixer', null, null, null, true);
        $rates = $curreny->getRates('USD');
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
        $curreny = new \danielme85\CConverter\Currency('currencylayer', null, null, null, true);
        $this->assertNotEmpty($curreny);
    }

    /**
     * Test Currency conversion default config with CurrencyLayer as source.
     * @group currencylayer
     *
     * @return void
     */
    public function testConvertCurrencyLayer() {
        $curreny = new \danielme85\CConverter\Currency('currencylayer', null, null, null, true);
        $this->assertEquals(1, $curreny->convert('USD', 'USD', 1));
    }

    /**
     * Test getting Currency rates with CurrencyLayer as source.
     * @group currencylayer
     *
     * @return void
     */
    public function testGetRatesCurrencyLayer() {
        $curreny = new \danielme85\CConverter\Currency('currencylayer', null, null, null, true);
        $rates = $curreny->getRates('USD');
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
        $curreny = new \danielme85\CConverter\Currency('yahoo', null, null, null, true);
        $this->assertNotEmpty($curreny);
    }

    /**
     * Test Currency conversion default config with Yahoo Finance as source.
     * @group yahoo
     *
     * @return void
     */
    public function testConvertYahoo() {
        $curreny = new \danielme85\CConverter\Currency('yahoo', null, null, null, true);
        $this->assertEquals(1, $curreny->convert('USD', 'USD', 1));
    }

    /**
     * Test getting Currency rates with Yahoo Finance as source.
     * @group yahoo
     *
     * @return void
     */
    public function testGetRatesYahoo() {
        $curreny = new \danielme85\CConverter\Currency('yahoo', null, null, null, true);
        $rates = $curreny->getRates('USD');
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
        $curreny = new \danielme85\CConverter\Currency('openexchange', null, null, null, true);
        $this->assertNotEmpty($curreny);
    }

    /**
     * Test Currency conversion default config with OpenExchange as source.
     * @group openexchange
     *
     * @return void
     */
    public function testConvertOpenExchange() {
        $curreny = new \danielme85\CConverter\Currency('openexchange', null, null, null, true);
        $this->assertEquals(1, $curreny->convert('USD', 'USD', 1));
    }

    /**
     * Test getting Currency rates with OpenExchange as source.
     * @group openexchange
     *
     * @return void
     */
    public function testGetRatesOpenExchange() {
        $curreny = new \danielme85\CConverter\Currency('openexchange', null, null, null, true);
        $rates = $curreny->getRates('USD');
        $this->assertArrayHasKey('rates', $rates);
        $this->assertGreaterThan(0, $this->count($rates['rates']));
    }

}