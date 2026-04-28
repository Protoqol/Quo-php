<?php

use Protoqol\Quo\Quo;

if (!function_exists('quo')) {
    /**
     * Send variables to Quo.
     *
     * @return void variables
     */
    function quo(): void
    {
        try {
            // PHP/Quo will most likely never be fast enough that it will cause a collision.
            $groupingHash = hash("crc32b", microtime(true));

            $args = func_get_args();

            foreach ($args as $index => $arg) {
                Quo::make($arg, $index, $groupingHash);
            }
        } catch (Exception $e) {
            // Ignore error for now
            return;
        }
    }
} else {
    /**
     * Alternative fn name; Send variables to Quo.
     *
     * @return array variables
     */
    function _quo(): array
    {
        try {
            // PHP/Quo will most likely never be fast enough that it will cause a collision.
            $groupingHash = hash("crc32b", microtime(true));

            $args = func_get_args();

            foreach ($args as $index => $arg) {
                Quo::make($arg, $index, $groupingHash);
            }

            return $args;
        } catch (Exception $e) {
            return [];
        }
    }
}
