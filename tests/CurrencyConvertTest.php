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
     * @group basics
     *
     * @return void
     */
    public function testDefaultApi() {
        $currency = new Currency(null, null, false, null, true);

        $rates = $currency->getRates();
        $this->assertNotEmpty($rates);
        $this->assertEquals(1, $rates['USD']);

        $this->assertEquals(8.47, $currency->convert('USD', 'NOK', 1));
        $this->assertEquals(846.94, $currency->convert('USD', 'NOK', 100));

        $this->assertEquals(0.88, $currency->convert('USD', 'EUR', 1));
        $this->assertEquals(87.57, $currency->convert('USD', 'EUR', 100));

        $this->assertEquals(10, $currency->convert('USD', 'USD', 10));
        $this->assertEquals(10, $currency->convert('EUR', 'EUR', 10));
        $this->assertEquals(10, $currency->convert('NOK', 'NOK', 10));
    }

    /**
     * Test the static function "shortcuts"
     *
     * @group basics
     *
     * @return void
     */
    public function testStaticShortCalls() {

        $usrates = Currency::rates(null, null, null, null, true, 5, true);
        $this->assertEquals(1, $usrates['USD']);

        $eurorates = Currency::rates('EUR', null, null, null, true, 5, true);
        $this->assertEquals(1, $eurorates['EUR']);

        $this->assertEquals(1, Currency::conv('USD', 'USD', 1, null, null, null, null, true, 5, true));

    }

    /**
     * Test to see if the Currency object can be created with The European Central Bank
     * @group eurobank
     *
     * @return void
     */
    public function testEuroBank()
    {
        $currency = new Currency('eurocentralbank', null, false, null, true);
        $this->assertNotEmpty($currency->getRates());
        $this->assertEquals(1, $currency->convert('USD', 'USD', 1));
        $this->assertEquals(1, $currency->convert('EUR', 'EUR', 1));

        //Test with live data
        $currency = new Currency('eurocentralbank');
        $this->assertNotEmpty($currency->getRates());

    }

    /**
     * Test to see if the Currency object can be created with fixer.
     * @group fixer
     *
     * @return void
     */
    public function testFixer()
    {
        $currency = new Currency('fixer', null, false, null, true);
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
    public function testCurrencyLayer()
    {
        $currency = new Currency('currencylayer', null, false, null, true);
        $this->assertNotEmpty($currency->getRates());
        $this->assertEquals(1, $currency->convert('USD', 'USD', 1));
        $this->assertEquals(1, $currency->convert('EUR', 'EUR', 1));

    }

    /**
     * Test to see if the Currency object can be created with Yahoo Finance.
     * @group yahoo
     *
     * @return void
     */
    public function testYahoo()
    {
        $currency = new Currency('yahoo', null, false, null, true);
        $this->assertNotEmpty($currency->getRates());
        $this->assertEquals(1, $currency->convert('USD', 'USD', 1));
        $this->assertEquals(1, $currency->convert('EUR', 'EUR', 1));
    }

    /**
     * Test to see if the Currency object can be created with OpenExchange.
     * @group openexchange
     *
     * @return void
     */
    public function testOpenExchange()
    {
        $currency = new Currency('openexchange', null, false, null, true);
        $this->assertNotEmpty($currency->getRates());
        $this->assertEquals(1, $currency->convert('USD', 'USD', 1));
        $this->assertEquals(1, $currency->convert('EUR', 'EUR', 1));
    }

    /**
     * Test the money formatter.
     * @group money
     *
     * @return void
     */
    public function testMoneyFormat() {
        $currency = new Currency(null, null, false, null, true);
        $this->assertEquals('$10.00', $currency->convert('USD','USD', 10, 'money'));
        $this->assertEquals('$199.99', moneyFormat(199.99, 'USD'));
        $this->assertEquals('kr 8,47', $currency->convert('USD','NOK', 1, 'money'));
    }
}