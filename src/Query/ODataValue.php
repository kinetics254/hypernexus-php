<?php

namespace KTL\Hypernexus\Query;

use DateTimeInterface;

final class ODataValue
{
    public static function format(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return "'" . $value->format('Y-m-d\TH:i:s') . "'";
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            if (self::isGuid($value)) {
                return $value;
            }

            return "'" . self::escapeString($value) . "'";
        }

        return "'" . self::escapeString((string) $value) . "'";
    }

    public static function isGuid(string $value): bool
    {
        return preg_match(
                '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',
                $value
            ) === 1;
    }

    private static function escapeString(string $value): string
    {
        return str_replace("'", "''", $value);
    }
}