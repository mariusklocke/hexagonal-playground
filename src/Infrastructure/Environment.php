<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

class Environment
{
    /**
     * Returns the string value for an environment variable
     *
     * @param string $name
     * @return string|null
     */
    public static function get(string $name): ?string
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Cannot get environment variable with empty name');
        }
        $value = getenv($name);
        if (is_string($value)) {
            return $value;
        }
        if (is_array($value)) {
            throw new \UnexpectedValueException('Ambiguous value for environment variable ' . $name);
        }

        return null;
    }

    /**
     * @param array $names
     * @return array
     */
    public static function getArray(array $names): array
    {
        $result = [];
        foreach ($names as $name) {
            $value = self::get($name);
            if (null !== $value) {
                $result[$name] = self::get($name);
            }
        }

        return $result;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public static function set(string $name, string $value): void
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Cannot set environment variable with empty name');
        }
        if (!putenv("$name=$value")) {
            throw new \RuntimeException('Failed to set environment variable ' . $name);
        }
    }

    /**
     * @param array $config
     */
    public static function setArray(array $config): void
    {
        foreach ($config as $key => $value) {
            if (null !== $value) {
                self::set($key, $value);
            }
        }
    }
}