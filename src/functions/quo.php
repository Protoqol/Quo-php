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
        return Quo::make(func_get_args());
    }
} else {
    throw new Exception('The function `quo` has already been defined elsewhere.');
}
