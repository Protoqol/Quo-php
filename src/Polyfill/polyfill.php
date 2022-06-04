<?php

use Protoqol\Quo\Polyfill\Php72;


/**
 * Check if $value is countable.
 *
 * @param $value
 *
 * @return bool
 */
if (!function_exists('is_countable')) {
    function is_countable($value): bool
    {
        return is_array($value) || $value instanceof Countable;
    }
}
/**
 * Check if $haystack contains $needle.
 *
 * @param string $haystack
 * @param string $needle
 *
 * @return bool
 */
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}

/**
 * Check $haystack starts with $needle.
 *
 * @param string $haystack
 * @param string $needle
 *
 * @return bool
 */
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle): bool
    {
        return 0 === strncmp($haystack, $needle, \strlen($needle));
    }
}

/**
 * Check $haystack ends with $needle.
 *
 * @param string $haystack
 * @param string $needle
 *
 * @return bool
 */
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle): bool
    {
        if ('' === $needle || $needle === $haystack) {
            return true;
        }

        if ('' === $haystack) {
            return false;
        }

        $needleLength = \strlen($needle);

        return $needleLength <= \strlen($haystack) && 0 === substr_compare($haystack, $needle, -$needleLength);
    }
}

/**
 * Get debug type for $value.
 *
 * @param $value
 *
 * @return string
 */
if (!function_exists('get_debug_type')) {
    function get_debug_type($value): string
    {
        switch (true) {
            case null === $value:
                return 'null';
            case \is_bool($value):
                return 'bool';
            case \is_string($value):
                return 'string';
            case \is_array($value):
                return 'array';
            case \is_int($value):
                return 'int';
            case \is_float($value):
                return 'float';
            case \is_object($value):
                break;
            case $value instanceof \__PHP_Incomplete_Class:
                return '__PHP_Incomplete_Class';
            default:
                if (null === $type = @get_resource_type($value)) {
                    return 'unknown';
                }

                if ('Unknown' === $type) {
                    $type = 'closed';
                }

                return "resource ($type)";
        }

        $class = \get_class($value);

        if (false === strpos($class, '@')) {
            return $class;
        }

        return (get_parent_class($class) ?: key(class_implements($class)) ?: 'class') . '@anonymous';
    }
}

if ('\\' === \DIRECTORY_SEPARATOR && !function_exists('sapi_windows_vt100_support')) {
    function sapi_windows_vt100_support($stream, $enable = null): bool
    {
        return Php72::sapi_windows_vt100_support($stream, $enable);
    }
}

if (!function_exists('stream_isatty')) {
    function stream_isatty($stream): bool
    {
        return Php72::stream_isatty($stream);
    }
}

if (!function_exists('utf8_encode')) {
    function utf8_encode($string)
    {
        return Php72::utf8_encode($string);
    }
}

if (!function_exists('utf8_decode')) {
    function utf8_decode($string)
    {
        return Php72::utf8_decode($string);
    }
}

if (!function_exists('spl_object_id')) {
    function spl_object_id($object)
    {
        return Php72::spl_object_id($object);
    }
}

if (!function_exists('mb_ord')) {
    function mb_ord($string, $encoding = null)
    {
        return Php72::mb_ord($string, $encoding);
    }
}

if (!function_exists('mb_chr')) {
    function mb_chr($codepoint, $encoding = null)
    {
        return Php72::mb_chr($codepoint, $encoding);
    }
}

if (!function_exists('mb_scrub')) {
    function mb_scrub($string, $encoding = null)
    {
        $encoding = null === $encoding ? mb_internal_encoding() : $encoding;
        return mb_convert_encoding($string, $encoding, $encoding);
    }
}
