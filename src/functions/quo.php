<?php

use Protoqol\Quo\Config\QuoConfig;
use Protoqol\Quo\Quo;

if (!function_exists('quo')) {
    /**
     * Send variables to Quo.
     *
     * @return array variables
     */
    function quo(): array
    {
        try {
            return Quo::make(func_get_args());
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('quoc')) {
    /**
     * Custom Quo config.
     *
     * Used on a per-call basis, the parameters passed are not stored unless $store is set to true.
     *
     * @param string $hostname
     * @param int    $port
     * @param bool   $store
     *
     * @return QuoConfig
     */
    function quoc(string $hostname, int $port, bool $store = false): QuoConfig
    {
        return QuoConfig::custom($hostname, $port, $store);
    }
}
