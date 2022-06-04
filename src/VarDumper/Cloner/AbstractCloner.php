<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Protoqol\Quo\VarDumper\Cloner;

use Protoqol\Quo\VarDumper\Caster\Caster;
use Protoqol\Quo\VarDumper\Exception\ThrowingCasterException;

/**
 * AbstractCloner implements a generic caster mechanism for objects and resources.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
abstract class AbstractCloner implements ClonerInterface
{
    public static $defaultCasters = [
        '__PHP_Incomplete_Class' => ['Protoqol\Quo\VarDumper\Caster\Caster', 'castPhpIncompleteClass'],

        'Protoqol\Quo\VarDumper\Caster\CutStub' => ['Protoqol\Quo\VarDumper\Caster\StubCaster', 'castStub'],
        'Protoqol\Quo\VarDumper\Caster\CutArrayStub' => ['Protoqol\Quo\VarDumper\Caster\StubCaster', 'castCutArray'],
        'Protoqol\Quo\VarDumper\Caster\ConstStub' => ['Protoqol\Quo\VarDumper\Caster\StubCaster', 'castStub'],
        'Protoqol\Quo\VarDumper\Caster\EnumStub' => ['Protoqol\Quo\VarDumper\Caster\StubCaster', 'castEnum'],

        'Fiber' => ['Protoqol\Quo\VarDumper\Caster\FiberCaster', 'castFiber'],

        'Closure' => ['Protoqol\Quo\VarDumper\Caster\ReflectionCaster', 'castClosure'],
        'Generator' => ['Protoqol\Quo\VarDumper\Caster\ReflectionCaster', 'castGenerator'],
        'ReflectionType' => ['Protoqol\Quo\VarDumper\Caster\ReflectionCaster', 'castType'],
        'ReflectionAttribute' => ['Protoqol\Quo\VarDumper\Caster\ReflectionCaster', 'castAttribute'],
        'ReflectionGenerator' => ['Protoqol\Quo\VarDumper\Caster\ReflectionCaster', 'castReflectionGenerator'],
        'ReflectionClass' => ['Protoqol\Quo\VarDumper\Caster\ReflectionCaster', 'castClass'],
        'ReflectionClassConstant' => ['Protoqol\Quo\VarDumper\Caster\ReflectionCaster', 'castClassConstant'],
        'ReflectionFunctionAbstract' => ['Protoqol\Quo\VarDumper\Caster\ReflectionCaster', 'castFunctionAbstract'],
        'ReflectionMethod' => ['Protoqol\Quo\VarDumper\Caster\ReflectionCaster', 'castMethod'],
        'ReflectionParameter' => ['Protoqol\Quo\VarDumper\Caster\ReflectionCaster', 'castParameter'],
        'ReflectionProperty' => ['Protoqol\Quo\VarDumper\Caster\ReflectionCaster', 'castProperty'],
        'ReflectionReference' => ['Protoqol\Quo\VarDumper\Caster\ReflectionCaster', 'castReference'],
        'ReflectionExtension' => ['Protoqol\Quo\VarDumper\Caster\ReflectionCaster', 'castExtension'],
        'ReflectionZendExtension' => ['Protoqol\Quo\VarDumper\Caster\ReflectionCaster', 'castZendExtension'],

        'Doctrine\Common\Persistence\ObjectManager' => ['Protoqol\Quo\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Doctrine\Common\Proxy\Proxy' => ['Protoqol\Quo\VarDumper\Caster\DoctrineCaster', 'castCommonProxy'],
        'Doctrine\ORM\Proxy\Proxy' => ['Protoqol\Quo\VarDumper\Caster\DoctrineCaster', 'castOrmProxy'],
        'Doctrine\ORM\PersistentCollection' => ['Protoqol\Quo\VarDumper\Caster\DoctrineCaster', 'castPersistentCollection'],
        'Doctrine\Persistence\ObjectManager' => ['Protoqol\Quo\VarDumper\Caster\StubCaster', 'cutInternals'],

        'DOMException' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castException'],
        'DOMStringList' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMNameList' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMImplementation' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castImplementation'],
        'DOMImplementationList' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMNode' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castNode'],
        'DOMNameSpaceNode' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castNameSpaceNode'],
        'DOMDocument' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castDocument'],
        'DOMNodeList' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMNamedNodeMap' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMCharacterData' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castCharacterData'],
        'DOMAttr' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castAttr'],
        'DOMElement' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castElement'],
        'DOMText' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castText'],
        'DOMTypeinfo' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castTypeinfo'],
        'DOMDomError' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castDomError'],
        'DOMLocator' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castLocator'],
        'DOMDocumentType' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castDocumentType'],
        'DOMNotation' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castNotation'],
        'DOMEntity' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castEntity'],
        'DOMProcessingInstruction' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castProcessingInstruction'],
        'DOMXPath' => ['Protoqol\Quo\VarDumper\Caster\DOMCaster', 'castXPath'],

        'XMLReader' => ['Protoqol\Quo\VarDumper\Caster\XmlReaderCaster', 'castXmlReader'],

        'ErrorException' => ['Protoqol\Quo\VarDumper\Caster\ExceptionCaster', 'castErrorException'],
        'Exception' => ['Protoqol\Quo\VarDumper\Caster\ExceptionCaster', 'castException'],
        'Error' => ['Protoqol\Quo\VarDumper\Caster\ExceptionCaster', 'castError'],
        'Symfony\Bridge\Monolog\Logger' => ['Protoqol\Quo\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Protoqol\Quo\DependencyInjection\ContainerInterface' => ['Protoqol\Quo\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Protoqol\Quo\EventDispatcher\EventDispatcherInterface' => ['Protoqol\Quo\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Protoqol\Quo\HttpClient\AmpHttpClient' => ['Protoqol\Quo\VarDumper\Caster\SymfonyCaster', 'castHttpClient'],
        'Protoqol\Quo\HttpClient\CurlHttpClient' => ['Protoqol\Quo\VarDumper\Caster\SymfonyCaster', 'castHttpClient'],
        'Protoqol\Quo\HttpClient\NativeHttpClient' => ['Protoqol\Quo\VarDumper\Caster\SymfonyCaster', 'castHttpClient'],
        'Protoqol\Quo\HttpClient\Response\AmpResponse' => ['Protoqol\Quo\VarDumper\Caster\SymfonyCaster', 'castHttpClientResponse'],
        'Protoqol\Quo\HttpClient\Response\CurlResponse' => ['Protoqol\Quo\VarDumper\Caster\SymfonyCaster', 'castHttpClientResponse'],
        'Protoqol\Quo\HttpClient\Response\NativeResponse' => ['Protoqol\Quo\VarDumper\Caster\SymfonyCaster', 'castHttpClientResponse'],
        'Protoqol\Quo\HttpFoundation\Request' => ['Protoqol\Quo\VarDumper\Caster\SymfonyCaster', 'castRequest'],
        'Protoqol\Quo\Uid\Ulid' => ['Protoqol\Quo\VarDumper\Caster\SymfonyCaster', 'castUlid'],
        'Protoqol\Quo\Uid\Uuid' => ['Protoqol\Quo\VarDumper\Caster\SymfonyCaster', 'castUuid'],
        'Protoqol\Quo\VarDumper\Exception\ThrowingCasterException' => ['Protoqol\Quo\VarDumper\Caster\ExceptionCaster', 'castThrowingCasterException'],
        'Protoqol\Quo\VarDumper\Caster\TraceStub' => ['Protoqol\Quo\VarDumper\Caster\ExceptionCaster', 'castTraceStub'],
        'Protoqol\Quo\VarDumper\Caster\FrameStub' => ['Protoqol\Quo\VarDumper\Caster\ExceptionCaster', 'castFrameStub'],
        'Protoqol\Quo\VarDumper\Cloner\AbstractCloner' => ['Protoqol\Quo\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Protoqol\Quo\ErrorHandler\Exception\SilencedErrorContext' => ['Protoqol\Quo\VarDumper\Caster\ExceptionCaster', 'castSilencedErrorContext'],

        'Imagine\Image\ImageInterface' => ['Protoqol\Quo\VarDumper\Caster\ImagineCaster', 'castImage'],

        'Ramsey\Uuid\UuidInterface' => ['Protoqol\Quo\VarDumper\Caster\UuidCaster', 'castRamseyUuid'],

        'ProxyManager\Proxy\ProxyInterface' => ['Protoqol\Quo\VarDumper\Caster\ProxyManagerCaster', 'castProxy'],
        'PHPUnit_Framework_MockObject_MockObject' => ['Protoqol\Quo\VarDumper\Caster\StubCaster', 'cutInternals'],
        'PHPUnit\Framework\MockObject\MockObject' => ['Protoqol\Quo\VarDumper\Caster\StubCaster', 'cutInternals'],
        'PHPUnit\Framework\MockObject\Stub' => ['Protoqol\Quo\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Prophecy\Prophecy\ProphecySubjectInterface' => ['Protoqol\Quo\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Mockery\MockInterface' => ['Protoqol\Quo\VarDumper\Caster\StubCaster', 'cutInternals'],

        'PDO' => ['Protoqol\Quo\VarDumper\Caster\PdoCaster', 'castPdo'],
        'PDOStatement' => ['Protoqol\Quo\VarDumper\Caster\PdoCaster', 'castPdoStatement'],

        'AMQPConnection' => ['Protoqol\Quo\VarDumper\Caster\AmqpCaster', 'castConnection'],
        'AMQPChannel' => ['Protoqol\Quo\VarDumper\Caster\AmqpCaster', 'castChannel'],
        'AMQPQueue' => ['Protoqol\Quo\VarDumper\Caster\AmqpCaster', 'castQueue'],
        'AMQPExchange' => ['Protoqol\Quo\VarDumper\Caster\AmqpCaster', 'castExchange'],
        'AMQPEnvelope' => ['Protoqol\Quo\VarDumper\Caster\AmqpCaster', 'castEnvelope'],

        'ArrayObject' => ['Protoqol\Quo\VarDumper\Caster\SplCaster', 'castArrayObject'],
        'ArrayIterator' => ['Protoqol\Quo\VarDumper\Caster\SplCaster', 'castArrayIterator'],
        'SplDoublyLinkedList' => ['Protoqol\Quo\VarDumper\Caster\SplCaster', 'castDoublyLinkedList'],
        'SplFileInfo' => ['Protoqol\Quo\VarDumper\Caster\SplCaster', 'castFileInfo'],
        'SplFileObject' => ['Protoqol\Quo\VarDumper\Caster\SplCaster', 'castFileObject'],
        'SplHeap' => ['Protoqol\Quo\VarDumper\Caster\SplCaster', 'castHeap'],
        'SplObjectStorage' => ['Protoqol\Quo\VarDumper\Caster\SplCaster', 'castObjectStorage'],
        'SplPriorityQueue' => ['Protoqol\Quo\VarDumper\Caster\SplCaster', 'castHeap'],
        'OuterIterator' => ['Protoqol\Quo\VarDumper\Caster\SplCaster', 'castOuterIterator'],
        'WeakReference' => ['Protoqol\Quo\VarDumper\Caster\SplCaster', 'castWeakReference'],

        'Redis' => ['Protoqol\Quo\VarDumper\Caster\RedisCaster', 'castRedis'],
        'RedisArray' => ['Protoqol\Quo\VarDumper\Caster\RedisCaster', 'castRedisArray'],
        'RedisCluster' => ['Protoqol\Quo\VarDumper\Caster\RedisCaster', 'castRedisCluster'],

        'DateTimeInterface' => ['Protoqol\Quo\VarDumper\Caster\DateCaster', 'castDateTime'],
        'DateInterval' => ['Protoqol\Quo\VarDumper\Caster\DateCaster', 'castInterval'],
        'DateTimeZone' => ['Protoqol\Quo\VarDumper\Caster\DateCaster', 'castTimeZone'],
        'DatePeriod' => ['Protoqol\Quo\VarDumper\Caster\DateCaster', 'castPeriod'],

        'GMP' => ['Protoqol\Quo\VarDumper\Caster\GmpCaster', 'castGmp'],

        'MessageFormatter' => ['Protoqol\Quo\VarDumper\Caster\IntlCaster', 'castMessageFormatter'],
        'NumberFormatter' => ['Protoqol\Quo\VarDumper\Caster\IntlCaster', 'castNumberFormatter'],
        'IntlTimeZone' => ['Protoqol\Quo\VarDumper\Caster\IntlCaster', 'castIntlTimeZone'],
        'IntlCalendar' => ['Protoqol\Quo\VarDumper\Caster\IntlCaster', 'castIntlCalendar'],
        'IntlDateFormatter' => ['Protoqol\Quo\VarDumper\Caster\IntlCaster', 'castIntlDateFormatter'],

        'Memcached' => ['Protoqol\Quo\VarDumper\Caster\MemcachedCaster', 'castMemcached'],

        'Ds\Collection' => ['Protoqol\Quo\VarDumper\Caster\DsCaster', 'castCollection'],
        'Ds\Map' => ['Protoqol\Quo\VarDumper\Caster\DsCaster', 'castMap'],
        'Ds\Pair' => ['Protoqol\Quo\VarDumper\Caster\DsCaster', 'castPair'],
        'Protoqol\Quo\VarDumper\Caster\DsPairStub' => ['Protoqol\Quo\VarDumper\Caster\DsCaster', 'castPairStub'],

        'mysqli_driver' => ['Protoqol\Quo\VarDumper\Caster\MysqliCaster', 'castMysqliDriver'],

        'CurlHandle' => ['Protoqol\Quo\VarDumper\Caster\ResourceCaster', 'castCurl'],
        ':curl' => ['Protoqol\Quo\VarDumper\Caster\ResourceCaster', 'castCurl'],

        ':dba' => ['Protoqol\Quo\VarDumper\Caster\ResourceCaster', 'castDba'],
        ':dba persistent' => ['Protoqol\Quo\VarDumper\Caster\ResourceCaster', 'castDba'],

        'GdImage' => ['Protoqol\Quo\VarDumper\Caster\ResourceCaster', 'castGd'],
        ':gd' => ['Protoqol\Quo\VarDumper\Caster\ResourceCaster', 'castGd'],

        ':mysql link' => ['Protoqol\Quo\VarDumper\Caster\ResourceCaster', 'castMysqlLink'],
        ':pgsql large object' => ['Protoqol\Quo\VarDumper\Caster\PgSqlCaster', 'castLargeObject'],
        ':pgsql link' => ['Protoqol\Quo\VarDumper\Caster\PgSqlCaster', 'castLink'],
        ':pgsql link persistent' => ['Protoqol\Quo\VarDumper\Caster\PgSqlCaster', 'castLink'],
        ':pgsql result' => ['Protoqol\Quo\VarDumper\Caster\PgSqlCaster', 'castResult'],
        ':process' => ['Protoqol\Quo\VarDumper\Caster\ResourceCaster', 'castProcess'],
        ':stream' => ['Protoqol\Quo\VarDumper\Caster\ResourceCaster', 'castStream'],

        'OpenSSLCertificate' => ['Protoqol\Quo\VarDumper\Caster\ResourceCaster', 'castOpensslX509'],
        ':OpenSSL X.509' => ['Protoqol\Quo\VarDumper\Caster\ResourceCaster', 'castOpensslX509'],

        ':persistent stream' => ['Protoqol\Quo\VarDumper\Caster\ResourceCaster', 'castStream'],
        ':stream-context' => ['Protoqol\Quo\VarDumper\Caster\ResourceCaster', 'castStreamContext'],

        'XmlParser' => ['Protoqol\Quo\VarDumper\Caster\XmlResourceCaster', 'castXml'],
        ':xml' => ['Protoqol\Quo\VarDumper\Caster\XmlResourceCaster', 'castXml'],

        'RdKafka' => ['Protoqol\Quo\VarDumper\Caster\RdKafkaCaster', 'castRdKafka'],
        'RdKafka\Conf' => ['Protoqol\Quo\VarDumper\Caster\RdKafkaCaster', 'castConf'],
        'RdKafka\KafkaConsumer' => ['Protoqol\Quo\VarDumper\Caster\RdKafkaCaster', 'castKafkaConsumer'],
        'RdKafka\Metadata\Broker' => ['Protoqol\Quo\VarDumper\Caster\RdKafkaCaster', 'castBrokerMetadata'],
        'RdKafka\Metadata\Collection' => ['Protoqol\Quo\VarDumper\Caster\RdKafkaCaster', 'castCollectionMetadata'],
        'RdKafka\Metadata\Partition' => ['Protoqol\Quo\VarDumper\Caster\RdKafkaCaster', 'castPartitionMetadata'],
        'RdKafka\Metadata\Topic' => ['Protoqol\Quo\VarDumper\Caster\RdKafkaCaster', 'castTopicMetadata'],
        'RdKafka\Message' => ['Protoqol\Quo\VarDumper\Caster\RdKafkaCaster', 'castMessage'],
        'RdKafka\Topic' => ['Protoqol\Quo\VarDumper\Caster\RdKafkaCaster', 'castTopic'],
        'RdKafka\TopicPartition' => ['Protoqol\Quo\VarDumper\Caster\RdKafkaCaster', 'castTopicPartition'],
        'RdKafka\TopicConf' => ['Protoqol\Quo\VarDumper\Caster\RdKafkaCaster', 'castTopicConf'],
    ];

    protected $maxItems = 2500;
    protected $maxString = -1;
    protected $minDepth = 1;

    /**
     * @var array<string, list<callable>>
     */
    private $casters = [];

    /**
     * @var callable|null
     */
    private $prevErrorHandler;

    private $classInfo = [];
    private $filter = 0;

    /**
     * @param callable[]|null $casters A map of casters
     *
     * @see addCasters
     */
    public function __construct(array $casters = null)
    {
        if (null === $casters) {
            $casters = static::$defaultCasters;
        }
        $this->addCasters($casters);
    }

    /**
     * Adds casters for resources and objects.
     *
     * Maps resources or objects types to a callback.
     * Types are in the key, with a callable caster for value.
     * Resource types are to be prefixed with a `:`,
     * see e.g. static::$defaultCasters.
     *
     * @param callable[] $casters A map of casters
     */
    public function addCasters(array $casters)
    {
        foreach ($casters as $type => $callback) {
            $this->casters[$type][] = $callback;
        }
    }

    /**
     * Sets the maximum number of items to clone past the minimum depth in nested structures.
     */
    public function setMaxItems(int $maxItems)
    {
        $this->maxItems = $maxItems;
    }

    /**
     * Sets the maximum cloned length for strings.
     */
    public function setMaxString(int $maxString)
    {
        $this->maxString = $maxString;
    }

    /**
     * Sets the minimum tree depth where we are guaranteed to clone all the items.  After this
     * depth is reached, only setMaxItems items will be cloned.
     */
    public function setMinDepth(int $minDepth)
    {
        $this->minDepth = $minDepth;
    }

    /**
     * Clones a PHP variable.
     *
     * @param mixed $var    Any PHP variable
     * @param int   $filter A bit field of Caster::EXCLUDE_* constants
     *
     * @return Data
     */
    public function cloneVar($var, int $filter = 0)
    {
        $this->prevErrorHandler = set_error_handler(function ($type, $msg, $file, $line, $context = []) {
            if (\E_RECOVERABLE_ERROR === $type || \E_USER_ERROR === $type) {
                // Cloner never dies
                throw new \ErrorException($msg, 0, $type, $file, $line);
            }

            if ($this->prevErrorHandler) {
                return ($this->prevErrorHandler)($type, $msg, $file, $line, $context);
            }

            return false;
        });
        $this->filter = $filter;

        if ($gc = gc_enabled()) {
            gc_disable();
        }
        try {
            return new Data($this->doClone($var));
        } finally {
            if ($gc) {
                gc_enable();
            }
            restore_error_handler();
            $this->prevErrorHandler = null;
        }
    }

    /**
     * Effectively clones the PHP variable.
     *
     * @param mixed $var Any PHP variable
     *
     * @return array
     */
    abstract protected function doClone($var);

    /**
     * Casts an object to an array representation.
     *
     * @param bool $isNested True if the object is nested in the dumped structure
     *
     * @return array
     */
    protected function castObject(Stub $stub, bool $isNested)
    {
        $obj = $stub->value;
        $class = $stub->class;

        if (\PHP_VERSION_ID < 80000 ? "\0" === ($class[15] ?? null) : str_contains($class, "@anonymous\0")) {
            $stub->class = get_debug_type($obj);
        }
        if (isset($this->classInfo[$class])) {
            [$i, $parents, $hasDebugInfo, $fileInfo] = $this->classInfo[$class];
        } else {
            $i = 2;
            $parents = [$class];
            $hasDebugInfo = method_exists($class, '__debugInfo');

            foreach (class_parents($class) as $p) {
                $parents[] = $p;
                ++$i;
            }
            foreach (class_implements($class) as $p) {
                $parents[] = $p;
                ++$i;
            }
            $parents[] = '*';

            $r = new \ReflectionClass($class);
            $fileInfo = $r->isInternal() || $r->isSubclassOf(Stub::class) ? [] : [
                'file' => $r->getFileName(),
                'line' => $r->getStartLine(),
            ];

            $this->classInfo[$class] = [$i, $parents, $hasDebugInfo, $fileInfo];
        }

        $stub->attr += $fileInfo;
        $a = Caster::castObject($obj, $class, $hasDebugInfo, $stub->class);

        try {
            while ($i--) {
                if (!empty($this->casters[$p = $parents[$i]])) {
                    foreach ($this->casters[$p] as $callback) {
                        $a = $callback($obj, $a, $stub, $isNested, $this->filter);
                    }
                }
            }
        } catch (\Exception $e) {
            $a = [(Stub::TYPE_OBJECT === $stub->type ? Caster::PREFIX_VIRTUAL : '').'⚠' => new ThrowingCasterException($e)] + $a;
        }

        return $a;
    }

    /**
     * Casts a resource to an array representation.
     *
     * @param bool $isNested True if the object is nested in the dumped structure
     *
     * @return array
     */
    protected function castResource(Stub $stub, bool $isNested)
    {
        $a = [];
        $res = $stub->value;
        $type = $stub->class;

        try {
            if (!empty($this->casters[':'.$type])) {
                foreach ($this->casters[':'.$type] as $callback) {
                    $a = $callback($res, $a, $stub, $isNested, $this->filter);
                }
            }
        } catch (\Exception $e) {
            $a = [(Stub::TYPE_OBJECT === $stub->type ? Caster::PREFIX_VIRTUAL : '').'⚠' => new ThrowingCasterException($e)] + $a;
        }

        return $a;
    }
}
