<?php

namespace Protoqol\Quo\Http;

class QuoRequest
{
    /**
     * @var string
     */
    private $hostname;
    /**
     * @var int
     */
    private $port;

    /**
     * @var false|resource
     */
    private $client;

    /**
     * @var string
     */
    private $connectionError;

    /**
     * @var string[]
     */
    private $httpHeaders = [
        "Accept: application/json",
        "Content-Type: application/json",
    ];

    /**
     * @var string
     */
    private $userAgent = "Protoqol/Quo";

    /**
     * @param        $requester
     * @param string $hostname
     * @param int    $port
     * @param bool   $verbose
     */
    public function __construct($requester, string $hostname, int $port, bool $verbose = false)
    {
        $this->hostname = $hostname;
        $this->port     = $port;
        $this->client   = $requester;
        $this->setHeaders();

        if ($verbose) {
            curl_setopt($this->client, CURLOPT_VERBOSE, true);
        }
    }

    /**
     * Set request headers.
     *
     * @param array $parameters
     *
     * @return bool
     */
    public function setHeaders(array $parameters = []): bool
    {
        return curl_setopt_array(
            $this->client,
            $parameters + [
                CURLOPT_URL            => "http://{$this->hostname}:{$this->port}/quo-tunnel",
                CURLOPT_HEADER         => true,
                CURLOPT_POST           => true,
                CURLOPT_FRESH_CONNECT  => true,
                CURLOPT_FORBID_REUSE   => true,
                CURLOPT_TIMEOUT        => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT      => $this->userAgent,
                CURLOPT_HTTPHEADER     => $this->httpHeaders,
            ]
        );
    }

    /**
     * Set request body.
     *
     * @param QuoPayload $payload
     *
     * @return bool
     */
    public function setBody(QuoPayload $payload): bool
    {
        return curl_setopt($this->client, CURLOPT_POSTFIELDS, $payload->toJson());
    }

    /**
     * Get error messages from curl.
     *
     * @return string
     */
    public function getError(): string
    {
        return $this->connectionError;
    }

    /**
     * Send request.
     *
     * @return bool|string
     */
    public function send()
    {
        $response = curl_exec($this->client);

        $this->captureErrors();

        return $response;
    }

    /**
     * Capture errors before curl connection is closed.
     *
     * @return void
     */
    private function captureErrors()
    {
        $this->connectionError = curl_error($this->client);
    }
}
