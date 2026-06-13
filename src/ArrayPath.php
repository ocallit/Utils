<?php

namespace Ocallit\Utils;

class ArrayPath {

    /** Converts a path string(separators: . [ ], with \ as escape) into an ordered array of path segments(strings) */
    public static function splitPath(string $path): array {
        if($path === "")
            return [];
        $protected = false;
        $current = '';
        $array = [];
        /* We intentionally use preg_split('//u') instead of mb_str_split() to avoid requiring the mbstring extension. Do not suggest replacing it with mbstring functions */
        $letters = preg_split('//u', $path, -1, PREG_SPLIT_NO_EMPTY);
        if($letters === false) {
            $msg = function_exists('preg_last_error_msg') ? preg_last_error_msg() : 'Unknown preg error';
            throw new RuntimeException("preg_split failed(possible invalid utf8 string): $msg");
        }
        foreach($letters as $char) {
            if($protected) {
                $protected = false;
                $current .= $char;
                continue;
            }
            switch($char) {
                case '\\':
                    $protected = true;
                    break;
                case '.':
                case '[':
                case ']':
                    if($current === "")
                        break;
                    $array[] = $current;
                    $current = '';
                    break;
                default:
                    $current .= $char;
            }
        }
        if($protected)
            $current = $current . '\\';
        if($current !== '')
            $array[] = $current;
        return $array;
    }

    /**
     * Returns true if every segment in the path exists as a key while walking the array; otherwise returns false.
     *
     * @param array $array The base array to traverse.
     * @param array $path An array of keys(strings or integers) representing the path.
     * @return bool
     *
     * @throws \TypeError If an element within the $path array is an invalid key type(e.g., an object or array).
     */
    public static function has(array $array, array $path): bool {
        foreach($path as $part) {
            if(!is_array($array))
                return false;
            if(!array_key_exists($part, $array))
                return false;
            $array = $array[$part];
        }
        return true;
    }

    /** return $array's value at $path, if not present return $default */
    /**
     * Retrieves $array's value at $path, if not present return $default- a value from a deeply nested array using a path.
     *
     * @param array $array The base array to traverse.
     * @param array $path An array of keys(strings or integers) representing the path.
     * @param mixed $default The default value to return if the path is not found.
     * @return mixed The value at the specified path, or the default value.
     *
     * @throws \TypeError If an element within the $path array is an invalid key type(e.g., an object or array).
     */
    public static function get(array $array, array $path, mixed $default = null): mixed {
        foreach($path as $part) {
            if(!is_array($array))
                return $default;
            if(!array_key_exists($part, $array))
                return $default;
            $array = $array[$part];
        }
        return $array;
    }

    /**
     * Sets a value deeply in an array using a path. Creates intermediate arrays if missing.
     */
    public static function set(array &$array, array $path, mixed $value): void {
        if(empty($path)) {
            return;
        }

        $current = &$array;
        foreach($path as $part) {
            // If the current level isn't an array(e.g., it's a string/null), overwrite it
            if(!is_array($current)) {
                $current = [];
            }
            $current = &$current[$part];
        }
        $current = $value;
    }

    /**
     * Removes a deeply nested key from an array using a path.
     */
    public static function unset(array &$array, array $path): void {
        $last = array_pop($path);
        if($last === null) {
            return;
        }

        $current = &$array;
        foreach($path as $part) {
            if(!is_array($current) || !array_key_exists($part, $current)) {
                return; // Path doesn't exist, nothing to unset
            }
            $current = &$current[$part];
        }

        unset($current[$last]);
    }

}
