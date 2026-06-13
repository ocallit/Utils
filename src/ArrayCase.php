<?php

namespace Ocallit\Utils;

class ArrayCase {
    /**
     * case-insensitive array key exists check, array_key_insensitive_exists
     */
    function has(string $key, array $array): bool {
        if(array_key_exists($key, $array)) {
            return true;
        }

        foreach($array as $k => $_) {
            if(is_string($k) && strcasecmp($k, $key) === 0) {
                return true;
            }
        }

        return false;
    }

    function key(string $key, array $array): int|string|null {
        if(array_key_exists($key, $array)) {
            return $key;
        }

        foreach($array as $k => $_) {
            if(is_string($k) && strcasecmp($k, $key) === 0) {
                return $key;
            }
        }

        return null;
    }

    /**
     * case-insensitive array value retrieval.
     */
    function get(string $key, array $array, mixed $default = null): mixed {
        if(array_key_exists($key, $array)) {
            return $array[$key];
        }
        foreach($array as $k => $v) {
            if(is_string($k) && strcasecmp($k, $key) === 0) {
                return $v;
            }
        }

        return $default;
    }
}

