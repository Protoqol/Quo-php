<?php

namespace Protoqol\Quo\Info;

class QuoRuntime
{
    /**
     * Get current runtime (OS and Architecture).
     *
     * @return string
     */
    public static function get(): string
    {
        return PHP_OS . '-' . (PHP_INT_SIZE === 8 ? 'x64' : 'x86');
    }
}
