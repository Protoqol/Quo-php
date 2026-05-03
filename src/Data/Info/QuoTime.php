<?php

namespace Protoqol\Quo\Data\Info;

class QuoTime
{
    /**
     * Get current time in milliseconds.
     *
     * @return int
     */
    public static function get(): int
    {
        return (int) floor(microtime(true) * 1000);
    }
}
