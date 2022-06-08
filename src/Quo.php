<?php

namespace Protoqol\Quo;

use Exception;
use Protoqol\Quo\Config\QuoConfig;
use Protoqol\Quo\Http\QuoCurlHandle;
use Protoqol\Quo\Http\QuoPayload;
use Protoqol\Quo\Http\QuoRequest;
use Protoqol\Quo\Http\QuoResponse;
use Protoqol\Quo\VarDumper\VarDumper;

class Quo
{
    /**
     * @var QuoRequest $request
     */
    private $request;

    /**
     * @param           $requester
     * @param QuoConfig $config
     *
     * @throws Exception
     */
    public function __construct($requester, QuoConfig $config)
    {
        $this->request = new QuoRequest($requester, $config->getHostname(), $config->getPort());
    }

    /**
     * Make new quo instance.
     *
     * @return array|mixed
     * @throws Exception
     */
    public static function make()
    {
        $args = func_get_arg(0);

        if (empty($args)) {
            return [];
        }

        $config = new QuoConfig();

        if ($config->get('general.ENABLED') == 0) {
            return [];
        }

        $requestEntropy = mt_rand(11, 9999);

        $requester = QuoCurlHandle::make();

        $quo = new Quo($requester, $config);

        foreach ($args as $argument) {
            try {
                ob_start();
                VarDumper::dump(is_string($argument) ? strip_tags($argument) : $argument);
                $dump = ob_get_contents();
                ob_end_clean();
                if (!QuoResponse::responseOk($response = $quo->send($dump, $requestEntropy))) {
                    // Response was not as expected
                    // var_dump($response);
                }
            } catch (Exception $e) {
                // Something probably went wrong with the VarDumper.
                // var_dump($e);
            }
        }

        QuoCurlHandle::destroy($requester);

        return $args;
    }

    /**
     * Send to Quo Client.
     *
     * @param string $dump
     *
     * @return bool|string
     */
    private function send(string $dump, $requestEntropy)
    {
        $this->request->setBody(QuoPayload::make($dump, $requestEntropy));

        $response = $this->request->send();

        if ($err = $this->request->getError()) {
            // Possible causes.
            // - Client is not running.
            // - Network access blocked.
            // var_dump($err);
        }

        return $response;
    }
}
