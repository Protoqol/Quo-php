<?php

namespace Protoqol\Quo\Config;

use Exception;
use Protoqol\Quo\Exceptions\QuoConfigException;

class QuoConfig
{
    /**
     * Default, presumed, location of ini.
     *
     * @var string
     */
    private $defaultIniLocation;

    /**
     * @var string
     */
    private $hostname;

    /**
     * @var int
     */
    private $port;

    /**
     * @var
     */
    private $cache;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->cache = new QuoCacheConfig();

        if (!$this->hasConfig()) {
            $this->defaultIniLocation = get_quo_cache_path() . "quo-internal-config.ini";
            $this->hostname           = $this->getHostname(true);
            $this->port               = $this->getPort(true);
        }
    }

    /**
     * Make default instance of QuoConfig.
     *
     * @return QuoConfig
     * @throws Exception
     */
    public static function make(): QuoConfig
    {
        return new self();
    }

    /**
     * Get value from config by key.
     *
     * @param string $key
     *
     * @return mixed|null
     * @throws Exception
     */
    public function get(string $key)
    {
        $file = $this->defaultIniLocation;

        if (file_exists($file) && is_readable($file)) {
            $ini = parse_ini_file($file, true);
        } else {
            throw new QuoConfigException('Config file not readable or missing at: ' . $file);
        }

        if (!str_contains($key, '.')) {
            return $ini[$key] ?? null;
        }

        $split = explode('.', $key);

        return $ini[$split[0]][$split[1]] ?? null;
    }

    /**
     * Set value in meta/quo-config.ini.
     *
     * @param string $key
     * @param        $value
     *
     * @return bool
     * @throws QuoConfigException
     */
    public function set(string $key, $value): bool
    {
        $file = $this->defaultIniLocation;

        $str = "";

        if (file_exists($file) && is_writable($file)) {
            $ini = parse_ini_file($file, true);
        } else {
            throw new QuoConfigException('Config file not writeable or missing at: ' . $file);
        }

        foreach ($ini as $sectionName => $section) {
            $str .= "\r\n[$sectionName]\r\n";
            foreach ($section as $entry => $val) {
                if ($key === $sectionName . '.' . $entry) {
                    $str .= $entry . ' = ' . $value . "\r\n";
                } else {
                    $str .= $entry . ' = ' . $val . "\r\n";
                }
            }
        }

        return (bool)file_put_contents($file, $str);
    }

    /**
     * Get hostname.
     *
     * @param bool $ini
     *
     * @return string
     * @throws Exception
     */
    public function getHostname(bool $ini = false): string
    {
        return (string)($ini ? $this->get('http.HOSTNAME') : $this->hostname);
    }

    /**
     * Get port.
     *
     * @param bool $ini
     *
     * @return int
     * @throws Exception
     */
    public function getPort(bool $ini = false): int
    {
        return (int)($ini ? $this->get('http.PORT') : $this->port);
    }

    /**
     * Look for custom config in project.
     *
     * @return bool
     * @throws Exception
     */
    private function hasConfig(): bool
    {
        if ($customConfigPath = $this->cache->getCache('CUSTOM_CONFIG_PATH')) {
            $this->defaultIniLocation = $customConfigPath;
            $this->hostname           = $this->getHostname(true);
            $this->port               = $this->getPort(true);
            return true;
        }

        // If <entry-point>.php is in root dir.
        $firstCheck = getcwd() . DIRECTORY_SEPARATOR . "quo-config.ini";

        // If <entry-point>.php is in a public directory
        $secondCheck = dirname(getcwd()) . DIRECTORY_SEPARATOR . "quo-config.ini";

        if (file_exists($firstCheck)) {
            $this->cache->setCache('CUSTOM_CONFIG_PATH', $firstCheck);
            return $this->hasConfig();
        }

        if (file_exists($secondCheck)) {
            $this->cache->setCache('CUSTOM_CONFIG_PATH', $secondCheck);
            return $this->hasConfig();
        }

        return false;
    }
}
