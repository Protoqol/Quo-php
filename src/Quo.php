<?php

namespace Protoqol\Quo;

use DateTime;
use Exception;

class Quo
{
    private $client;
    /**
     * @var string
     */
    private $hostname;
    /**
     * @var int
     */
    private $port;

    /**
     * @param string $hostname
     * @param int    $port
     */
    public function __construct(string $hostname = "127.0.0.1", int $port = 8118)
    {
        $this->hostname = $hostname;
        $this->port     = $port;
    }

    /**
     * Send to Quo Client.
     *
     * @param string $dump
     * @param int    $id
     *
     * @return bool|string
     * @throws Exception
     */
    public function sendToQuoClient(string $dump, int $id = 0)
    {
        $dump         = base64_encode($dump);
        $title        = $this->getFileAndLineNumber();
        $variableName = $this->getVariableNames();
        $calltag      = $this->getCalltag($variableName);
        $time         = $this->getTimeOfRequest();
        $request      = curl_init();

        curl_setopt_array($request, [
            CURLOPT_URL            => "http://{$this->hostname}:{$this->port}/quo-tunnel",
            CURLOPT_HEADER         => true,
            CURLOPT_POST           => true,
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_FORBID_REUSE   => true,
            CURLOPT_TIMEOUT        => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT      => "Protoqol/Quo (v0.1.0)",
            CURLOPT_HTTPHEADER     => [
                "Accept: application/json",
                "Content-Type: application/json",
                "X-Quo-Token: " . hash('md5', time()),
            ],
            CURLOPT_POSTFIELDS     => json_encode([
                'id'           => $id,
                'callTag'      => $calltag,
                "dump"         => $dump,
                "backtrace"    => $title,
                "time"         => $time,
                'variableName' => $variableName,
            ]),
        ]);

        $response = curl_exec($request);

        if ($err = curl_error($request)) {
            // Possible causes.
            // - Client is not running.
            // - Network access blocked.
            // var_dump($err);
        }

        curl_close($request);

        return $response;
    }

    /**
     * Get timestamp.
     *
     * @return string
     */
    public function getTimeOfRequest(): string
    {
        return (new DateTime)->format('H:i:s');
    }

    /**
     * Get calltag for call, this is unique per quo() call and can be used to identify grouped calls.
     *
     * @param $varName
     *
     * @return false|string
     */
    public function getCalltag($varName)
    {
        return hash("md5", $varName);
    }

    /**
     * Get file and line number.
     *
     * @return string
     */
    public function getFileAndLineNumber(): string
    {
        $backtrace = debug_backtrace()[2];
        return $backtrace['file'] . ':' . $backtrace['line'];
    }

    /**
     * Get variable name(s) from backtrace.
     *
     * @return string
     */
    public function getVariableNames(): string
    {
        $backtrack = 2;

        $backtrace = debug_backtrace();
        $src       = (file($backtrace[$backtrack]['file']))[$backtrace[$backtrack]['line'] - 1];
        $multiLine = !str_contains($src, 'quo(');

        if ($multiLine) {
            $src = "";

            $i = 1;
            while (!str_contains($src, 'quo(')) {
                $src .= (file($backtrace[$backtrack]['file']))[$backtrace[$backtrack]['line'] - $i] . ($i === 1 ? "," : "");
                $i++;
            }
        }

        $pattern      = '#(.*)quo *?\( *?(.*) *?\)(.*)#i';
        $match        = preg_replace($pattern, '$2', $src);
        $variableName = trim(str_replace("quo(", "", preg_replace('/\s+/', '', $match)));

        $variableNames = explode(",", $variableName);
        $variableName  = implode(",", $multiLine ? array_reverse($variableNames) : $variableNames);

        if (count($variableNames) > 1) {
            $variableName = $multiLine ? substr($variableName, 1) : $variableName;
        }

        return preg_replace("/QuoConfig::set\(.*\),/", "", $variableName);
    }
}
