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

use Protoqol\Quo\VarDumper\Cloner\Stub;

use const XML_ERROR_ASYNC_ENTITY;
use const XML_ERROR_ATTRIBUTE_EXTERNAL_ENTITY_REF;
use const XML_ERROR_BAD_CHAR_REF;
use const XML_ERROR_BINARY_ENTITY_REF;
use const XML_ERROR_DUPLICATE_ATTRIBUTE;
use const XML_ERROR_EXTERNAL_ENTITY_HANDLING;
use const XML_ERROR_INCORRECT_ENCODING;
use const XML_ERROR_INVALID_TOKEN;
use const XML_ERROR_JUNK_AFTER_DOC_ELEMENT;
use const XML_ERROR_MISPLACED_XML_PI;
use const XML_ERROR_NO_ELEMENTS;
use const XML_ERROR_NO_MEMORY;
use const XML_ERROR_NONE;
use const XML_ERROR_PARAM_ENTITY_REF;
use const XML_ERROR_PARTIAL_CHAR;
use const XML_ERROR_RECURSIVE_ENTITY_REF;
use const XML_ERROR_SYNTAX;
use const XML_ERROR_TAG_MISMATCH;
use const XML_ERROR_UNCLOSED_CDATA_SECTION;
use const XML_ERROR_UNCLOSED_TOKEN;
use const XML_ERROR_UNDEFINED_ENTITY;
use const XML_ERROR_UNKNOWN_ENCODING;

/**
 * Casts XML resources to array representation.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 *
 * @final
 */
class XmlResourceCaster
{
    public const XML_ERRORS = [
        XML_ERROR_NONE                          => 'XML_ERROR_NONE',
        XML_ERROR_NO_MEMORY                     => 'XML_ERROR_NO_MEMORY',
        XML_ERROR_SYNTAX                        => 'XML_ERROR_SYNTAX',
        XML_ERROR_NO_ELEMENTS                   => 'XML_ERROR_NO_ELEMENTS',
        XML_ERROR_INVALID_TOKEN                 => 'XML_ERROR_INVALID_TOKEN',
        XML_ERROR_UNCLOSED_TOKEN                => 'XML_ERROR_UNCLOSED_TOKEN',
        XML_ERROR_PARTIAL_CHAR                  => 'XML_ERROR_PARTIAL_CHAR',
        XML_ERROR_TAG_MISMATCH                  => 'XML_ERROR_TAG_MISMATCH',
        XML_ERROR_DUPLICATE_ATTRIBUTE           => 'XML_ERROR_DUPLICATE_ATTRIBUTE',
        XML_ERROR_JUNK_AFTER_DOC_ELEMENT        => 'XML_ERROR_JUNK_AFTER_DOC_ELEMENT',
        XML_ERROR_PARAM_ENTITY_REF              => 'XML_ERROR_PARAM_ENTITY_REF',
        XML_ERROR_UNDEFINED_ENTITY              => 'XML_ERROR_UNDEFINED_ENTITY',
        XML_ERROR_RECURSIVE_ENTITY_REF          => 'XML_ERROR_RECURSIVE_ENTITY_REF',
        XML_ERROR_ASYNC_ENTITY                  => 'XML_ERROR_ASYNC_ENTITY',
        XML_ERROR_BAD_CHAR_REF                  => 'XML_ERROR_BAD_CHAR_REF',
        XML_ERROR_BINARY_ENTITY_REF             => 'XML_ERROR_BINARY_ENTITY_REF',
        XML_ERROR_ATTRIBUTE_EXTERNAL_ENTITY_REF => 'XML_ERROR_ATTRIBUTE_EXTERNAL_ENTITY_REF',
        XML_ERROR_MISPLACED_XML_PI              => 'XML_ERROR_MISPLACED_XML_PI',
        XML_ERROR_UNKNOWN_ENCODING              => 'XML_ERROR_UNKNOWN_ENCODING',
        XML_ERROR_INCORRECT_ENCODING            => 'XML_ERROR_INCORRECT_ENCODING',
        XML_ERROR_UNCLOSED_CDATA_SECTION        => 'XML_ERROR_UNCLOSED_CDATA_SECTION',
        XML_ERROR_EXTERNAL_ENTITY_HANDLING      => 'XML_ERROR_EXTERNAL_ENTITY_HANDLING',
    ];

    public static function castXml($h, array $a, Stub $stub, bool $isNested)
    {
        $a['current_byte_index']    = xml_get_current_byte_index($h);
        $a['current_column_number'] = xml_get_current_column_number($h);
        $a['current_line_number']   = xml_get_current_line_number($h);
        $a['error_code']            = xml_get_error_code($h);

        if (isset(self::XML_ERRORS[$a['error_code']])) {
            $a['error_code'] = new ConstStub(self::XML_ERRORS[$a['error_code']], $a['error_code']);
        }

        return $a;
    }
}
