<?php

namespace Protoqol\Quo\Data;

use Composer\InstalledVersions;
use Protoqol\Quo\Data\Info\QuoRuntime;
use Protoqol\Quo\Data\Info\QuoStackTrace;
use Protoqol\Quo\Data\Info\QuoSystemUsage;
use Protoqol\Quo\Data\Info\QuoThread;
use Protoqol\Quo\Data\Info\QuoTime;
use Ramsey\Uuid\Uuid;

class QuoPayload
{
    /**
     * @var mixed
     */
    private $variable;

    /**
     * @var string|null
     */
    private $variableName;

    /**
     * @var string|null
     */
    private $groupingHash;

    /**
     * @var int
     */
    private $argumentIndex;

    /**
     * @var array
     */
    private $backtrace;

    /**
     * @param  mixed  $variable
     * @param  int  $argumentIndex
     * @param  string|null  $groupingHash
     */
    public function __construct($variable, int $argumentIndex = 0, ?string $groupingHash = null)
    {
        $this->variable      = $variable;
        $this->argumentIndex = $argumentIndex;
        $this->backtrace     = debug_backtrace();
        $this->variableName  = $this->getVariableName();
        $this->groupingHash  = $groupingHash;
    }

    /**
     * Get called variable from quo(...$args).
     *
     * @return string|null
     */
    private function getVariableName(): ?string
    {
        $frame = $this->getCallerFrame();

        if (!isset($frame['file'], $frame['line']) || !$frame || !file_exists($frame['file'])) {
            return null;
        }

        $fileContent = file($frame['file']);
        $line        = $fileContent[$frame['line'] - 1];

        // Basic parsing to find quo(...) or Quo::make(...) call and its arguments
        if (preg_match('/(?:\bquo|_quo|Quo::make)\s*\((.*)\)/i', $line, $matches)) {
            $argsStr = $matches[1];

            // Naive split by comma, ignoring commas in strings/nested calls for now
            $args = array_map('trim', explode(',', $argsStr));

            return $args[$this->argumentIndex] ?? $args[0] ?? null;
        }

        return null;
    }

    /**
     * Find the first frame outside of Quo's internal code.
     *
     * @return array|null
     */
    private function getCallerFrame(): ?array
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        $internalClasses = [
            'Protoqol\\Quo\\Quo',
            'Protoqol\\Quo\\Data\\QuoPayload',
            'Protoqol\\Quo\\Http\\QuoRequest',
            'Protoqol\\Quo\\Http\\QuoCurlHandle',
        ];

        foreach ($this->backtrace as $i => $frame) {
            $class    = $frame['class'] ?? null;
            $function = $frame['function'] ?? null;

            if ($class && in_array($class, $internalClasses, true)) {
                continue;
            }

            if (!$class && in_array($function, ['quo', '_quo'])) {
                return $frame;
            }

            // If we are here, we might have passed all internal frames.
            // Check if the previous frame was one of ours.
            if ($i > 0) {
                $prevFrame = $this->backtrace[$i - 1];
                $prevClass = $prevFrame['class'] ?? null;
                $prevFunc  = $prevFrame['function'] ?? null;

                $prevClassCheck = $prevClass && in_array($prevClass, $internalClasses, true);
                $prevFuncCheck  = in_array($prevFunc, ['quo', '_quo'], true);

                if ($prevClassCheck || $prevFuncCheck) {
                    return $frame;
                }
            }
        }

        return $this->backtrace[count($this->backtrace) - 1] ?? null;
    }

    /**
     * Make QuoPayload instance.
     *
     * @param  mixed  $variable
     * @param  int  $argumentIndex
     * @param  string|null  $groupingHash
     *
     * @return QuoPayload
     */
    public static function make($variable, int $argumentIndex = 0, ?string $groupingHash = null): self
    {
        return new self($variable, $argumentIndex, $groupingHash);
    }

    /**
     * Get payload as a JSON encoded string.
     *
     * @return false|string
     */
    public function toJson()
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
        $varType     = $this->getVariableType();
        $varName     = $this->variableName ?? ($varType === "string" ? "\"{$this->getVariableValue()}\"" : (string) $this->getVariableValue());

        return [
            "meta"     => [
                "id"              => $this->getId(),
                "uid"             => $this->getCalltag(),
                "origin"          => $this->getSenderDomain(),
                "sender_origin"   => $this->getFileAndLineNr(),
                "time_epoch_ms"   => QuoTime::get(),
                "variable"        => [
                    "var_type"       => $varType,
                    "name"           => $varName,
                    "value"          => $varType === "string" ? "\"{$this->getVariableValue()}\"" : (string) $this->getVariableValue(),
                    "is_mutable"     => true,
                    "is_constant"    => defined($varName),
                    "is_expression"  => $this->isExpression($varName),
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
    private function getVariableType(): string
    {
        if (function_exists('get_debug_type')) {
            $type = get_debug_type($this->variable);
        } else {
            $type = is_object($this->variable) ? get_class($this->variable) : gettype($this->variable);

            $map = [
                'integer' => 'int',
                'boolean' => 'bool',
                'double'  => 'float',
            ];

            $type = $map[$type] ?? $type;
        }

        if ($type === 'array') {
            $type .= '<' . implode(', ', array_map(function ($item) {
                    if (function_exists('get_debug_type')) {
                        return get_debug_type($item);
                    }

                    $itemType = is_object($item) ? get_class($item) : gettype($item);

                    $map = [
                        'integer' => 'int',
                        'boolean' => 'bool',
                        'double'  => 'float',
                    ];

                    return $map[$itemType] ?? $itemType;
                }, $this->variable)) . '>';
        }

        return $type;
    }

    /**
     * @return mixed
     */
    private function getVariableValue()
    {
        if (is_scalar($this->variable)) {
            return $this->variable;
        }

        $displayed = json_encode($this->variable);

        return str_replace([':', '{', '}'], [' => ', '[', ']'], $displayed);
    }

    /**
     * @return int
     */
    private function getId(): int
    {
        return $this->argumentIndex;
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
        if (PHP_SAPI === 'cli' && $this->isInsidePsysh()) {
            if (isset($GLOBALS['argv']) && in_array('tinker', $GLOBALS['argv'], true)) {
                return "Tinker session";
            }

            return "PsySH session";
        }

        $frame = $this->getCallerFrame();

        if (!isset($frame['file'], $frame['line']) || !$frame) {
            return 'unknown:0';
        }

        return $frame['file'] . ':' . $frame['line'];
    }

    private function isInsidePsysh(): bool
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
            if (isset($frame['class']) && str_starts_with($frame['class'], 'Psy\\')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  string  $varName
     *
     * @return bool
     */
    private function isExpression(string $varName): bool
    {
        return strpos($varName, '$') !== 0;
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
