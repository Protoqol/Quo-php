<?php

namespace Protoqol\Quo\Http;

class QuoResponse
{
    /**
     * Check if response contains expected body.
     *
     * @param string $jsonResponse
     *
     * @return bool
     */
    public static function responseOk(string $jsonResponse): bool
    {
        return str_contains($jsonResponse, '{"message": "ok"');
    }
}
