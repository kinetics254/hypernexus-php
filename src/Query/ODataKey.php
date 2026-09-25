<?php

namespace KTL\Hypernexus\Query;

final class ODataKey
{
    public static function format(string|array $keys): string
    {
        if (is_string($keys)) {
            return '(' . ODataValue::format($keys) . ')';
        }

        $parts = [];

        foreach ($keys as $field => $value) {
            $parts[] = sprintf(
                '%s=%s',
                $field,
                ODataValue::format($value)
            );
        }

        return '(' . implode(',', $parts) . ')';
    }
}