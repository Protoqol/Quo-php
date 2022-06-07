<?php

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

if (!function_exists('get_quo_cache_path')) {
    /**
     * Get quo cache file path
     *
     * @return string
     */
    function get_quo_cache_path(): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "meta" . DIRECTORY_SEPARATOR;
    }
}
