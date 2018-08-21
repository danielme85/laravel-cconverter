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
     * Test of default settings and init
     * @group test
     *
     * @return void
     */
    public function testDefaultApi() {
        $currency = new Currency(null, null, null, null, true);

        $rates = $currency->getRates();
        $this->assertNotEmpty($rates);
        $this->assertEquals(1, $rates['rates']['USD']);

        $this->assertEquals(8.47, $currency->convert('USD', 'NOK', 1));
        $this->assertEquals(0.88, $currency->convert('USD', 'EUR', 1));
        $this->assertEquals(1, $currency->convert('USD', 'USD', 1));
        $this->assertEquals(1, $currency->convert('EUR', 'EUR', 1));
        $this->assertEquals(1, $currency->convert('NOK', 'NOK', 1));
    }

    /**
     * @group short
     *
     */
    public function testStaticShortCalls() {

        $usrates = Currency::rates(null, null, null, null, null, null, true);
        $this->assertEquals(1, $usrates['rates']['USD']);

        $eurorates = Currency::rates('EUR', null, null, null, null, null, true);
        $this->assertEquals(1, $eurorates['rates']['EUR']);

    }

    /**
     * Test to see if the Currency object can be created with The European Central Bank
     * @group eurobank
     *
     * @return void
     */
    public function testEuroBank()
    {
        $currency = new Currency('eurocentralbank', null, null, null, true);
        $this->assertNotEmpty($currency->getRates());
        $this->assertEquals(1, $currency->convert('USD', 'USD', 1));
        $this->assertEquals(1, $currency->convert('EUR', 'EUR', 1));

    }


    /**
     * Test to see if the Currency object can be created with fixer.
     * @group fixer
     *
     * @return void
     */
    public function testFixer()
    {
        $currency = new Currency('fixer', null, null, null, true);
        $this->assertNotEmpty($currency->getRates());
        $this->assertEquals(1, $currency->convert('USD', 'USD', 1));
        $this->assertEquals(1, $currency->convert('EUR', 'EUR', 1));
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