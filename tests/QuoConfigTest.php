<?php

use PHPUnit\Framework\TestCase;
use Protoqol\Quo\Config\QuoConfig;

class QuoConfigTest extends TestCase
{
    private static $instance;

    private static $testHost = '128.1.1.1';

    private static $testPort = 1234;

    public function setUp(): void
    {
        self::$instance = QuoConfig::load(__DIR__ . DIRECTORY_SEPARATOR . 'meta/quo-config-test.ini');
    }

    public function testCanLoadCustomIniFile()
    {
        $testIsLoadedExpected = 'TEST_CUSTOM_LOCATION';
        $testIsLoadedResult   = self::$instance->get('general.DISABLED_ON_DOMAIN');
        $this->assertEquals($testIsLoadedExpected, $testIsLoadedResult);
    }

    public function testMakeReturnsInstanceOfSelf()
    {
        $this->assertInstanceOf(
            QuoConfig::class,
            QuoConfig::make()
        );
    }

    public function testCanInjectCustomConfig()
    {
        $host = '10.0.0.1';
        $port = 1234;

        $instance = QuoConfig::custom($host, $port);

        $this->assertInstanceOf(
            QuoConfig::class,
            $instance
        );

        $this->assertEquals(
            $host,
            $instance->getHostname(),
            'Host received'
        );

        $this->assertEquals(
            $port,
            $instance->getPort(),
            'Port received'
        );
    }

    public function testCanReadConfigFile()
    {
        $instance      = QuoConfig::load(__DIR__ . DIRECTORY_SEPARATOR . 'meta/quo-config-test.ini');
        $expectedPort  = '7312';
        $actualReadVal = $instance::get('http.PORT');

        $this->assertEquals($expectedPort, $actualReadVal);
    }

    public function testCanWriteToConfigFile()
    {
        $instance = QuoConfig::load(__DIR__ . DIRECTORY_SEPARATOR . 'meta/quo-config-test.ini');
        $expected = 1234;
        $instance::set('http.PORT', $expected);
        $read = $instance::get('http.PORT');

        $this->assertEquals($expected, $read, 'Can change value in .ini');

        $instance::set('http.PORT', 7312);

        $this->assertEquals(7312, $instance::get('http.PORT'), 'Can revert it back.');
    }
}
