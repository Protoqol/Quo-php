<?php

namespace Protoqol\Quo\Data\Info;

class PhpError
{
    private $errorNumber;

    private $errorMessage;

    private $errorFile;

    private $errorLine;

    public function __construct($errNo, $errstr, $errFile, $errLine)
    {
        $this->errorNumber  = $errNo;
        $this->errorMessage = $errstr;
        $this->errorFile    = $errFile;
        $this->errorLine    = $errLine;
    }

    /**
     * @return mixed
     */
    public function getErrorLine()
    {
        return $this->errorLine;
    }

    /**
     * @return mixed
     */
    public function getErrorFile()
    {
        return $this->errorFile;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return mixed
     */
    public function getErrorNumber()
    {
        return $this->errorNumber;
    }
}
