<?php

use GuzzleHttp\Exception\GuzzleException;
use Protoqol\Quo\Quo;
use Protoqol\Quo\VarDumper\VarDumper;

if (!function_exists('quo')) {
    /**
     * @param var
     * @param mixed ...$moreVars
     *
     * @return array|mixed
     * @throws ErrorException
     * @throws GuzzleException
     */
    function quo($var, ...$moreVars)
    {
        $quo = new Quo("127.0.0.1", 8118);

        $id = $moreVars ? mt_rand(10, 100000) : 0;

        ob_start();
        VarDumper::dump($var);
        $dump = ob_get_contents();
        ob_end_clean();
        $quo->sendToQuoClient($dump, $id);

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

