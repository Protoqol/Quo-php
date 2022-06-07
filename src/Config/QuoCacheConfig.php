<?php

namespace Protoqol\Quo\Config;

class QuoCacheConfig
{
    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var string|false
     */
    private $filename;

    /**
     * @var string
     */
    private $cacheIni;

    public function __construct()
    {
        $this->timestamp = time();
        $this->cacheIni  = get_quo_cache_path();

        $files = scandir($this->cacheIni);
        foreach ($files as $file) {
            if (str_contains($file, 'quo-cache')) {
                $this->filename = $file;
                break;
            }
        }
    }

    /**
     * Set cache.
     *
     * @param string $key
     * @param        $value
     *
     * @return bool
     */
    public function setCache(string $key, $value): bool
    {
        return $this->set($key, $value);
    }

    /**
     * Get cache.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function getCache(string $key)
    {
        return $this->get($key);
    }

    /**
     * Update and verify cache.
     *
     * @return void
     */
    public function updateCache()
    {
        //
    }

    /**
     * Get value from meta/quo-config.ini by key.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    private function get(string $key)
    {
        $ini = parse_ini_file($this->cacheIni . $this->filename, true);

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
     */
    private function set(string $key, $value): bool
    {
        $ini = parse_ini_file($this->cacheIni . $this->filename, true);

        $str = "";

        foreach ($ini as $k => $v) {
            if ($key === $k) {
                $str .= $k . ' = ' . $value . "\r\n";
            } else {
                $str .= $k . ' = ' . $v . "\r\n";
            }
        }

        return (bool)file_put_contents($this->cacheIni . $this->filename, $str);
    }
}
