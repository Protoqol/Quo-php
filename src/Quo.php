<?php

namespace Protoqol\Quo;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class Quo
{
    private $client;

    public function __construct(string $hostname, int $port)
    {
        $this->client = new Client([
            'base_uri' => "http://$hostname:$port",
            'headers'  => [
                "User-Agent" => "Protoqol/Quo",
            ],
        ]);
    }

    /**
     * Send to Quo Client.
     *
     * @param string $dump
     * @param int    $id
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function sendToQuoClient(string $dump, int $id = 0): ResponseInterface
    {
        $backtrace1 = debug_backtrace()[1];
        $title      = $backtrace1['file'] . ':' . $backtrace1['line'];

        $backtrace = debug_backtrace();
        $src       = (file($backtrace[1]['file']))[$backtrace[1]['line'] - 1];
        $multiLine = !str_contains($src, 'quo(');

        if ($multiLine) {
            $src = "";

            $i = 1;
            while (!str_contains($src, 'quo(')) {
                $src .= (file($backtrace[1]['file']))[$backtrace[1]['line'] - $i] . ($i === 1 ? "," : "");
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

        return $this->client->post('/quo-tunnel', [
            'body' => json_encode([
                'id'           => $id,
                'callTag'      => hash("md5", $variableName),
                "dump"         => base64_encode($dump),
                "backtrace"    => $title,
                "time"         => (new \DateTime)->format('H:i:s'),
                'variableName' => $variableName,
            ]),
        ]);
    }
}
