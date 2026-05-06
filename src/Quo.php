<?php

namespace Protoqol\Quo;

use Exception;
use Protoqol\Quo\Config\QuoConfig;
use Protoqol\Quo\Data\Info\PhpError;
use Protoqol\Quo\Data\QuoErrorPayload;
use Protoqol\Quo\Data\QuoPayload;
use Protoqol\Quo\Http\QuoCurlHandle;
use Protoqol\Quo\Http\QuoRequest;
use Protoqol\Quo\Http\QuoResponse;

class Quo
{
    /**
     * @var QuoRequest $request
     */
    private $request;

    /**
     * @param           $requester
     * @param  QuoConfig  $config
     *
     * @throws Exception
     */
    public function __construct($requester, QuoConfig $config)
    {
        $this->request = new QuoRequest($requester, $config->getHostname(), $config->getPort());
    }

    /**
     * Create a new quo instance.
     *
     * @param  mixed  $arg
     * @param  int  $argumentIndex
     * @param  string|null  $groupingHash
     *
     * @return array
     * @throws Exception
     */
    public static function make($arg, int $argumentIndex = 0, ?string $groupingHash = null)
    {
        if (empty($arg) && $arg !== 0 && $arg !== false && $arg !== "" && $arg !== []) {
            return [];
        }

        $config = new QuoConfig();

        $requester = QuoCurlHandle::make();

        $quo = new self($requester, $config);

        QuoResponse::responseOk(
            $quo->send($arg, $argumentIndex, $groupingHash)
        );

        QuoCurlHandle::destroy($requester);

        return $arg;
    }

    /**
     * Send it to Quo Client.
     *
     * @param  mixed  $payload
     * @param  int  $argumentIndex
     * @param  string|null  $groupingHash
     *
     * @return bool|string
     */
    private function send($payload, int $argumentIndex = 0, ?string $groupingHash = null)
    {
        if ($payload instanceof PhpError) {
            $body = QuoErrorPayload::make($payload);
        } else {
            $body = QuoPayload::make($payload, $argumentIndex, $groupingHash);
        }

        $this->request->setBody($body);

        $response = $this->request->send();

        if ($err = $this->request->getError()) {
            // Possible causes. @TODO
            // - Client is not running.
            // - Wrong host:port.
            // - Network access blocked.
            var_dump($err);
        }

        return $response;
    }
}
