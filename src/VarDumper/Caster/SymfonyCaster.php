<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Protoqol\Quo\VarDumper\Caster;

use Protoqol\Quo\HttpFoundation\Request;
use Protoqol\Quo\Uid\Ulid;
use Protoqol\Quo\Uid\Uuid;
use Protoqol\Quo\VarDumper\Cloner\Stub;

use function array_key_exists;
use function count;
use function get_class;

/**
 * @final
 */
class SymfonyCaster
{
    public const REQUEST_GETTERS = [
        'pathInfo'   => 'getPathInfo',
        'requestUri' => 'getRequestUri',
        'baseUrl'    => 'getBaseUrl',
        'basePath'   => 'getBasePath',
        'method'     => 'getMethod',
        'format'     => 'getRequestFormat',
    ];

    public static function castRequest(Request $request, array $a, Stub $stub, bool $isNested)
    {
        $clone = null;

        foreach (self::REQUEST_GETTERS as $prop => $getter) {
            $key = Caster::PREFIX_PROTECTED . $prop;
            if (array_key_exists($key, $a) && null === $a[$key]) {
                if (null === $clone) {
                    $clone = clone $request;
                }
                $a[Caster::PREFIX_VIRTUAL . $prop] = $clone->{$getter}();
            }
        }

        return $a;
    }

    public static function castHttpClient($client, array $a, Stub $stub, bool $isNested)
    {
        $multiKey = sprintf("\0%s\0multi", get_class($client));
        if (isset($a[$multiKey])) {
            $a[$multiKey] = new CutStub($a[$multiKey]);
        }

        return $a;
    }

    public static function castHttpClientResponse($response, array $a, Stub $stub, bool $isNested)
    {
        $stub->cut += count($a);
        $a         = [];

        foreach ($response->getInfo() as $k => $v) {
            $a[Caster::PREFIX_VIRTUAL . $k] = $v;
        }

        return $a;
    }

    public static function castUuid(Uuid $uuid, array $a, Stub $stub, bool $isNested)
    {
        $a[Caster::PREFIX_VIRTUAL . 'toBase58'] = $uuid->toBase58();
        $a[Caster::PREFIX_VIRTUAL . 'toBase32'] = $uuid->toBase32();

        // symfony/uid >= 5.3
        if (method_exists($uuid, 'getDateTime')) {
            $a[Caster::PREFIX_VIRTUAL . 'time'] = $uuid->getDateTime()->format('Y-m-d H:i:s.u \U\T\C');
        }

        return $a;
    }

    public static function castUlid(Ulid $ulid, array $a, Stub $stub, bool $isNested)
    {
        $a[Caster::PREFIX_VIRTUAL . 'toBase58']  = $ulid->toBase58();
        $a[Caster::PREFIX_VIRTUAL . 'toRfc4122'] = $ulid->toRfc4122();

        // symfony/uid >= 5.3
        if (method_exists($ulid, 'getDateTime')) {
            $a[Caster::PREFIX_VIRTUAL . 'time'] = $ulid->getDateTime()->format('Y-m-d H:i:s.v \U\T\C');
        }

        return $a;
    }
}
