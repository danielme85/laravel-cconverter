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
     * A basic test example. <- just a test for the test to test if the test is testing.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    /**
     * Full integration test default config, conversion.
     *
     * @return void
     */

    public function testConversionDefault() {
        $currency = new \danielme85\CConverter\Currency();
        $this->assertEquals(1, $currency->convert('USD', 'USD', 1));
    }


    /**
     * Full integration test default config, currency rates.
     *
     * @return void
     */
    public function testRatesDefault() {
        $currency = new \danielme85\CConverter\Currency();
        $rates = $currency->getRates('USD');
        $this->assertArrayHasKey('rates', $rates);
        $this->assertGreaterThan(0, $this->count($rates['rates']));
    }

}