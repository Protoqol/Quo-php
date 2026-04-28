<?php

namespace Protoqol\Quo\Info;

class QuoStackTrace
{
    /**
     * Get stack trace and caller.
     *
     * @return array{frames: string[], caller: string|null}
     */
    public static function get(): array
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $frames    = [];
        $caller    = null;

        foreach ($backtrace as $frame) {
            $name = $frame['function'] ?? 'unknown';
            
            if (isset($frame['class'])) {
                $name = $frame['class'] . $frame['type'] . $name;
            }

            // Skip internal Quo calls
            if ($name === 'quo' || $name === '_quo' || str_contains($name, 'Protoqol\\Quo')) {
                continue;
            }

            if ($caller === null) {
                $caller = $name;
            }

            $frames[] = $name;
        }

        return [
            'frames' => $frames,
            'caller' => $caller,
        ];
    }
}
