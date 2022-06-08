<?php

namespace Protoqol\Quo\Http;

class QuoCurlHandle
{
    /**
     * Get curl instance.
     *
     * @return resource
     */
    public static function make()
    {
        return curl_init();
    }

    /**
     * Destroy curl instance.
     *
     * @param $handle
     *
     * @return void
     */
    public static function destroy($handle)
    {
        curl_close($handle);
    }
}
