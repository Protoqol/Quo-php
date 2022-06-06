<?php

namespace Protoqol\Quo;

use Exception;
use Protoqol\Quo\Config\QuoConfig;
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
     * @param QuoConfig $config
     */
    public function __construct(QuoConfig $config)
    {
        $this->request = new QuoRequest($config->getHostname(), $config->getPort());
    }

    /**
     * Make new quo instance.
     *
     * @return array|mixed
     * @throws Exception
     */
    public static function make()
    {
        // Disabled on this domain.
        if (QuoConfig::get('general.DISABLED_ON_DOMAIN') === gethostname()) {
            return [];
        }

        $args = func_get_arg(0);

        if (empty($args)) {
            return [];
        }

        if ($args[0] instanceof QuoConfig) {
            $quo = new Quo($args[0]);
            unset($args[0]);
        } else {
            try {
                $quo = new Quo(QuoConfig::make());
            } catch (Exception $e) {
                // Config error.
                // var_dump($e);
                return [];
            }
        }

        foreach ($args as $argument) {
            try {
                ob_start();
                VarDumper::dump($argument);
                $dump = ob_get_contents();
                ob_end_clean();
                if (!QuoResponse::responseOk($response = $quo->send($dump))) {
                    // Response was not as expected
                    // var_dump($response);
                }
            } catch (Exception $e) {
                // Something probably went wrong with the VarDumper.
                // var_dump($e);
            }
        }

        return $args;
    }

    /**
     * Send to Quo Client.
     *
     * @param string $dump
     *
     * @return bool|string
     */
    private function send(string $dump)
    {
        $this->request->setBody(QuoPayload::make($dump));

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
