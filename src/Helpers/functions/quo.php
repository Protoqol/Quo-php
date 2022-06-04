<?php

use Protoqol\Quo\Config\QuoConfig;
use Protoqol\Quo\Quo;
use Protoqol\Quo\VarDumper\VarDumper;

if (!function_exists('quo')) {
    /**
     * Pass all variables you want to send to
     *
     * @param mixed $var
     *
     * @return array|mixed
     * @throws ErrorException
     */
    function quo($var, ...$moreVars)
    {
        $quo = $var instanceof QuoConfig
            ? new Quo($var->getHostname(), $var->getPort())
            : new Quo();

        $id = $moreVars ? mt_rand(10, 100000) : 0;

        if (!$var instanceof QuoConfig) {
            ob_start();
            VarDumper::dump($var);
            $dump = ob_get_contents();
            ob_end_clean();
            $quo->sendToQuoClient($dump, $id);
        }

        foreach ($moreVars as $v) {
            ob_start();
            VarDumper::dump($v);
            $dump = ob_get_contents();
            ob_end_clean();
            $quo->sendToQuoClient($dump, $id);
        }

        if (1 < func_num_args()) {
            return func_get_args();
        }

        return $var;
    }
} else {
    throw new Exception('The function `quo` has already been defined elsewhere.');
}

