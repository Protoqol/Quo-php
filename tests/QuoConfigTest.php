<?php

use PHPUnit\Framework\TestCase;
use Protoqol\Quo\Config\QuoConfig;

class QuoConfigTest extends TestCase
{
    private $tempDir;

    public function testGetHostnameAndPortFromComposerJson(): void
    {
        $composerJson = [
            'extra' => [
                'quo-php' => [
                    'host' => '10.0.0.1',
                    'port' => 8888,
                ],
            ],
        ];
        file_put_contents($this->tempDir . DIRECTORY_SEPARATOR . 'composer.json', json_encode($composerJson));

        $config = new QuoConfig($this->tempDir);

        $this->assertEquals('10.0.0.1', $config->getHostname());
        $this->assertEquals(8888, $config->getPort());
    }

    public function testFallbacksWhenNoComposerJson(): void
    {
        $config = new QuoConfig($this->tempDir);

        $this->assertEquals('127.0.0.1', $config->getHostname());
        $this->assertEquals(7312, $config->getPort());
    }

    public function testFallbacksWhenNoExtraField(): void
    {
        file_put_contents($this->tempDir . DIRECTORY_SEPARATOR . 'composer.json', json_encode([]));

        $config = new QuoConfig($this->tempDir);

        $this->assertEquals('127.0.0.1', $config->getHostname());
        $this->assertEquals(7312, $config->getPort());
    }

    public function testGetEnabled(): void
    {
        $composerJson = [
            'extra' => [
                'quo-php' => [
                    'enabled' => 0,
                ],
            ],
        ];
        file_put_contents($this->tempDir . DIRECTORY_SEPARATOR . 'composer.json', json_encode($composerJson));

        $config = new QuoConfig($this->tempDir);
        $this->assertEquals(0, $config->get('general.ENABLED'));
    }

    public function testMake(): void
    {
        $config = QuoConfig::make($this->tempDir);
        $this->assertInstanceOf(QuoConfig::class, $config);
    }

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'quo_test_' . uniqid('', true);
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempDir . DIRECTORY_SEPARATOR . 'composer.json')) {
            unlink($this->tempDir . DIRECTORY_SEPARATOR . 'composer.json');
        }
        if (is_dir($this->tempDir)) {
            rmdir($this->tempDir);
        }
    }
}
