<?php

/**
 * Check if $value is countable.
 *
 * @param $value
 *
 * @return bool
 */
function is_countable($value): bool
{
    return is_array($value) || $value instanceof Countable || $value instanceof SimpleXmlElement;
}
