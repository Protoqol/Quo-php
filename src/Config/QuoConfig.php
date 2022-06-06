<?php

namespace Protoqol\Quo\Config;

use Protoqol\Quo\Exceptions\QuoConfigException;

class QuoConfig
{
    /**
     * @var string
     */
    private $hostname;

    /**
     * @var int
     */
    private $port;

    /**
     * Default, presumed, location of ini.
     *
     * @var string
     */
    private static $defaultIniLocation = 'meta/quo-config.ini';

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
     * @throws \Exception
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
     * @return void
     */
    public static function load(string $absoluteFilePath)
    {
        self::$defaultIniLocation = $absoluteFilePath;
    }

    /**
     * @param string $hostname
     * @param int    $port
     *
     * @return QuoConfig
     */
    public static function custom(string $hostname = 'localhost', int $port = 7312): QuoConfig
    {
        return new self($hostname, $port);
    }

    /**
     * Get hostname.
     *
     * @return string
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * Get port.
     *
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Get value from meta/quo-config.ini by key.
     *
     * @param string $key
     *
     * @return mixed|null
     * @throws \Exception
     */
    public static function get(string $key)
    {
        $file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . self::$defaultIniLocation;

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
    private static function set(string $key, $value): bool
    {
        $file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . self::$defaultIniLocation;
        $str  = "";

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
}
