<?php

namespace Protoqol\Quo\VarDumper;

use ErrorException;
use Protoqol\Quo\VarDumper\Caster\ReflectionCaster;
use Protoqol\Quo\VarDumper\Cloner\VarCloner;
use Protoqol\Quo\VarDumper\Dumper\HtmlDumper;

class VarDumper
{
    /**
     * @param $var
     *
     * @return string|null
     * @throws ErrorException
     */
    public static function dump($var): ?string
    {
        $cloner = new VarCloner();
        $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);
        $clonedVar = $cloner->cloneVar($var);

        $type     = $clonedVar->getType();
        $variable = $clonedVar->getValue();
        $count    = null;

        if (is_countable($variable) || is_string($variable)) {
            if (!is_string($variable)) {
                $count = " (" . count($variable) . ")";
            } else {
                $count = " (" . strlen($variable) . ")";
            }
        }

        $dumper = new HtmlDumper();
        $dumper->appendDumpPrefix(
            "<i data-searchable='" . self::searchableString($variable) . "' class='type-$type'>$type$count</i>"
        );

        return $dumper->dump($clonedVar);
    }

    /**
     * @param $var
     *
     * @return string
     */
    private static function searchableString($var): string
    {
        return htmlspecialchars(json_encode($var));
    }
}
