<?php

use Protoqol\Quo\Data\Info\PhpError;
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
            // PHP/Quo will most likely never be fast enough that this will cause a collision.
            $groupingHash = hash("crc32b", microtime(true));

            $args = func_get_args();

            foreach ($args as $index => $arg) {
                Quo::make($arg, $index, $groupingHash);
            }
        } catch (Throwable $e) {
            // Ignore error for now
            return;
        }
    }
} else {
    /**
     * Alternative fn name; Send variables to Quo.
     *
     * @return void
     */
    function _quo(): void
    {
        try {
            // PHP/Quo will most likely never be fast enough that this will cause a collision.
            $groupingHash = hash("crc32b", microtime(true));

            $args = func_get_args();

            foreach ($args as $index => $arg) {
                Quo::make($arg, $index, $groupingHash);
            }
        } catch (Throwable $e) {
            // Ignore error for now
            return;
        }
    }
}

function quo_error_handler(int $errno, string $errstr, string $errfile, int $errline): void
{
    try {
        $phpError = new PhpError($errno, $errstr, $errfile, $errline);

        Quo::make($phpError);
    } catch (Throwable $e) {
        // Ignore error for now
        return;
    }
}

function quo_exception_handler($e): void
{
    // @TODO
}
