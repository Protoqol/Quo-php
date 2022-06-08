<?php

namespace Protoqol\Quo\Http;

use DateTime;

class QuoPayload
{
    /**
     * @var string
     */
    private $dump;

    /**
     * @var string
     */
    private $encoding;

    /**
     * @var array|string|string[]|null
     */
    private $variables;

    /**
     * @param string $dump
     * @param string $encoding
     */
    public function __construct(string $dump, string $encoding = 'base64')
    {
        $this->dump      = $dump;
        $this->encoding  = $encoding;
        $this->variables = $this->getVariableNames();
    }

    /**
     * Get called variables from quo(...$args).
     *
     * @return array|string|string[]|null
     */
    private function getVariableNames()
    {
        // Amount of files to backtrack to.
        $backtrack = 5;

        $backtrace = debug_backtrace();
        $src       = (file($backtrace[$backtrack]['file']))[$backtrace[$backtrack]['line'] - 1];
        $multiLine = str_contains($src, 'quo(');

        if ($multiLine) {
            $src = "";

            $i = 1;

            while (!str_contains($src, 'quo(')) {
                $src .= (file(
                        $backtrace[$backtrack]['file']
                    ))[$backtrace[$backtrack]['line'] - $i] . ($i === 1 ? "," : "");
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

    /**
     * Make QuoPayload instance.
     *
     * @param string $dump
     *
     * @return QuoPayload
     */
    public static function make(string $dump): QuoPayload
    {
        return new self($dump);
    }

    /**
     * Get payload as json encoded string.
     *
     * @return false|string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Get payload as array.
     *
     * @return array[]
     */
    public function toArray(): array
    {
        return [
            "meta"    => [
                "id"             => $this->getId(),
                "uid"            => $this->getCalltag(),
                "origin"         => $this->getFileAndLineNr(),
                "senderOrigin"   => $this->getSenderDomain(),
                "time"           => $this->getCurrentTimestamp(),
                "calledVariable" => $this->variables,
            ],
            "payload" => $this->getDump(),
        ];
    }

    /**
     * @return int
     */
    private function getId(): int
    {
        $variables = count(explode(',', $this->variables));

        return $variables > 1 ? $variables : 0;
    }

    /**
     * @return false|string
     */
    private function getCalltag()
    {
        return hash("md5", $this->variables);
    }

    /**
     * @return string
     */
    private function getFileAndLineNr(): string
    {
        // Amount of files to backtrack to.
        $backtrack = 6;

        $backtrace = debug_backtrace()[$backtrack];
        return $backtrace['file'] . ':' . $backtrace['line'];
    }

    /**
     * Get domain this request was sent from.
     *
     * @return false|string
     */
    private function getSenderDomain()
    {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * Get current timestamp.
     *
     * @return string
     */
    private function getCurrentTimestamp(): string
    {
        return (new DateTime())->format('H:i:s');
    }

    /**
     * Get (encoded) dump.
     *
     * @param bool $disableEncoding
     *
     * @return string
     */
    private function getDump(bool $disableEncoding = false): string
    {
        switch ($this->encoding) {
            default:
            case 'base64':
                return $disableEncoding ? $this->dump : base64_encode($this->dump);
        }
    }
}
