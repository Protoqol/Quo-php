<?php

namespace Protoqol\Quo\Config;

class QuoConfig
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
     * @param string $hostname
     * @param int    $port
     */
    public function __construct($hostname, $port)
    {
        $this->hostname = $hostname;
        $this->port     = $port;
    }

    /**
     * @param string $hostname
     * @param int    $port
     *
     * @return QuoConfig
     */
    public static function set($hostname = 'localhost', $port = 8118)
    {
        return new self($hostname, $port);
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }
}
