<?php

declare(strict_types=1);

namespace App\Traits;

trait ConvertsToArray
{
    /**
     * Get the names in an array.
     *
     * @return array<int, string>
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Get the values in an array.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get the name values key pair array.
     *
     * @return array<string, string>
     */
    public static function array(): array
    {
        return array_combine(self::names(), self::values());
    }

    /**
     * Get the value values key pair array for select options.
     *
     * @return array<string, string>
     */
    public static function valuePairs(): array
    {
        return array_combine(self::values(), self::values());
    }

    /**
     * Get the associative array key pairs for quasar select options.
     */
    public static function quasarPairs(): array
    {
        $pairs = [];
        foreach (self::values() as $value) {
            $pairs[] = ['label' => $value, 'value' => $value];
        }

        return $pairs;
    }
}
