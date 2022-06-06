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
    private static $defaultIniLocation = 'meta/quo-config.ini';

    /**
     * Custom absolute ini location.
     *
     * @var null
     */
    private static $customIniLocation = null;

    /**
     * @var string
     */
    private $hostname;

    /**
     * @var int
     */
    private $port;

    /**
     * @param string $hostname
     * @param int    $port
     */
    public function __construct(string $hostname, int $port)
    {
        $this->hostname = $hostname;
        $this->port     = $port;
    }

    /**
     * Make default instance of QuoConfig.
     *
     * @return QuoConfig
     * @throws Exception
     */
    public static function make(): QuoConfig
    {
        return new self(self::get('http.HOSTNAME'), self::get('http.PORT'));
    }

    /**
     * Load config from file.
     *
     * @param string $absoluteFilePath
     *
     * @return QuoConfig
     * @throws Exception
     */
    public static function load(string $absoluteFilePath): QuoConfig
    {
        $instance                     = QuoConfig::make();
        $instance::$customIniLocation = $absoluteFilePath;
        return $instance;
    }

    /**
     * @param string $hostname
     * @param int    $port
     * @param bool   $store
     *
     * @return QuoConfig
     */
    public static function custom(string $hostname = 'localhost', int $port = 7312, bool $store = false): QuoConfig
    {
        if ($store) {
            self::set('http.HOST', $hostname);
            self::set('http.PORT', $port);
        }

        return new self($hostname, $port);
    }

    /**
     * Get value from meta/quo-config.ini by key.
     *
     * @param string $key
     *
     * @return mixed|null
     * @throws Exception
     */
    public static function get(string $key)
    {
        if (!self::$customIniLocation) {
            $file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . self::$defaultIniLocation;
        } else {
            $file = self::$customIniLocation;
        }

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
    public static function set(string $key, $value): bool
    {
        if (!self::$customIniLocation) {
            $file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . self::$defaultIniLocation;
        } else {
            $file = self::$customIniLocation;
        }

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
        return (string)($ini ? self::get('http.HOST') : $this->hostname);
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
        return (int)($ini ? self::get('http.PORT') : $this->port);
    }
}
