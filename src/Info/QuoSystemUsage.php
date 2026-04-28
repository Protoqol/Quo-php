<?php

namespace Protoqol\Quo\Info;

class QuoSystemUsage
{
    /**
     * Get system usage (CPU and Memory).
     *
     * @return array{cpu: float|null, memory: int|null}
     */
    public static function get(): array
    {
        $cpu = null;

        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();

            if ($load !== false && isset($load[0])) {
                $cpu = (float) $load[0];
            }
        }

        return [
            'cpu'    => $cpu,
            'memory' => memory_get_usage(),
        ];
    }
}
