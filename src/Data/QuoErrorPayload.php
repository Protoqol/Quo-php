<?php

namespace Protoqol\Quo\Data;

use Composer\InstalledVersions;
use Protoqol\Quo\Data\Info\PhpError;
use Protoqol\Quo\Data\Info\QuoRuntime;
use Protoqol\Quo\Data\Info\QuoStackTrace;
use Protoqol\Quo\Data\Info\QuoSystemUsage;
use Protoqol\Quo\Data\Info\QuoThread;
use Protoqol\Quo\Data\Info\QuoTime;
use Ramsey\Uuid\Uuid;

class QuoErrorPayload implements QuoPayloadInterface
{
    /**
     * @var mixed
     */
    private $variable;

    /**
     * @var string|null
     */
    private $groupingHash;

    /**
     * @var PhpError
     */
    private $error;

    /**
     * @param  PhpError  $error
     */
    public function __construct(PhpError $error)
    {
        $this->variable     = $error->getErrorMessage();
        $this->groupingHash = (string) time();
        $this->error        = $error;
    }

    /**
     * Create a QuoErrorPayload instance.
     *
     * @param  mixed  $variable
     *
     * @return QuoErrorPayload
     */
    public static function make($variable): self
    {
        return new self($variable);
    }

    /**
     * @return false|string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Get payload as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $stackTrace  = QuoStackTrace::get();
        $systemUsage = QuoSystemUsage::get();

        return [
            "meta"     => [
                "id"              => 0,
                "uid"             => $this->getCalltag(),
                "origin"          => $this->getSenderDomain(),
                "sender_origin"   => $this->getFileAndLineNr(),
                "time_epoch_ms"   => QuoTime::get(),
                "variable"        => [
                    "var_type"       => "quo-error-reporting-type",
                    "name"           => "Error",
                    "value"          => $this->error->getErrorMessage(),
                    "is_mutable"     => false,
                    "is_constant"    => false,
                    "is_expression"  => false,
                    "memory_address" => $this->getMemoryAddress(),
                    "grouping_hash"  => $this->groupingHash,
                ],
                "stack_trace"     => $stackTrace['frames'],
                "thread_info"     => QuoThread::get(),
                "runtime"         => QuoRuntime::get(),
                "cpu_usage"       => $systemUsage['cpu'],
                "memory_usage"    => $systemUsage['memory'],
                "caller_function" => $stackTrace['caller'],
            ],
            "language" => "php",
        ];
    }

    /**
     * @return string
     */
    private function getCalltag(): string
    {
        return (string) Uuid::uuid4();
    }

    /**
     * Get domain this request was sent from.
     *
     * @return false|string
     */
    private function getSenderDomain()
    {
        if (class_exists(InstalledVersions::class)) {
            $rootPackage = InstalledVersions::getRootPackage();

            return $rootPackage['name'] ?? $_SERVER['HTTP_HOST'] ?? 'PHP project';
        }

        return $_SERVER['HTTP_HOST'] ?? 'PHP project';
    }

    /**
     * @return string
     */
    private function getFileAndLineNr(): string
    {
        return $this->error->getErrorFile() . ':' . $this->error->getErrorLine();
    }

    /**
     * @return string
     */
    private function getMemoryAddress(): ?string
    {
        if (is_object($this->variable)) {
            return spl_object_hash($this->variable);
        }

        return null;
    }
}
