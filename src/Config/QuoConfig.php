<?php

namespace Protoqol\Quo\Config;

use Exception;
use RuntimeException;

class QuoConfig
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param  string|null  $basePath
     *
     * @throws Exception
     */
    public function __construct(?string $basePath = null)
    {
        $this->config = $this->loadConfig($basePath ?: getcwd());
    }

    /**
     * Load config from composer.json.
     *
     * @param  string  $startDir
     *
     * @return array
     */
    private function loadConfig(string $startDir): array
    {
        try {
            $composerPath = $this->findComposerJson($startDir);
            $composer     = json_decode(file_get_contents($composerPath), true);

            return $composer['extra']['quo-php'] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Find composer.json by traversing up.
     *
     * @param  string  $startDir
     *
     * @return string
     */
    private function findComposerJson(string $startDir): string
    {
        $dir = $startDir;

        while ($dir !== dirname($dir)) {
            if (file_exists($dir . DIRECTORY_SEPARATOR . 'composer.json')) {
                return $dir . DIRECTORY_SEPARATOR . 'composer.json';
            }
            $dir = dirname($dir);
        }

        if (file_exists($dir . DIRECTORY_SEPARATOR . 'composer.json')) {
            return $dir . DIRECTORY_SEPARATOR . 'composer.json';
        }

        throw new RuntimeException('composer.json not found');
    }

    /**
     * Make default instance of QuoConfig.
     *
     * @param  string|null  $basePath
     *
     * @return QuoConfig
     * @throws Exception
     */
    public static function make(?string $basePath = null): QuoConfig
    {
        return new self($basePath);
    }

    /**
     * Get value from config by key.
     *
     * @param  string  $key
     *
     * @return mixed|null
     */
    public function get(string $key)
    {
        if (strtoupper($key) === 'GENERAL.ENABLED') {
            return $this->config['enabled'] ?? 1;
        }

        if (strtoupper($key) === 'HTTP.HOSTNAME') {
            return $this->getHostname();
        }

        if (strtoupper($key) === 'HTTP.PORT') {
            return $this->getPort();
        }

        return $this->config[$key] ?? null;
    }

    /**
     * Get hostname.
     *
     * @param  bool  $unused
     *
     * @return string
     */
    public function getHostname(bool $unused = false): string
    {
        return $this->config['host'] ?? '127.0.0.1';
    }

    /**
     * Get port.
     *
     * @param  bool  $unused
     *
     * @return int
     */
    public function getPort(bool $unused = false): int
    {
        return (int) ($this->config['port'] ?? 7312);
    }
}
