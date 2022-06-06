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
