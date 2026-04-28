<?php

namespace Protoqol\Quo\Info;

class QuoHash
{
    /**
     * Get reproducible hash for grouping of same variables.
     *
     * @param  string  $varType
     * @param  string  $name
     * @param  string  $packageName
     *
     * @return string
     */
    public static function get(string $varType, string $name, string $packageName): string
    {
        $data = "{$varType}:{$name}:{$packageName}";

        if (in_array('xxh64', hash_algos(), true)) {
            return hash('xxh64', $data);
        }

        return sha1($data);
    }
}
