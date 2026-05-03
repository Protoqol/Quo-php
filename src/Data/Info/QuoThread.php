<?php

namespace Protoqol\Quo\Data\Info;

class QuoThread
{
    /**
     * Get current thread ID (Process ID for PHP).
     *
     * @return string
     */
    public static function get(): string
    {
        return (string) getmypid();
    }
}
