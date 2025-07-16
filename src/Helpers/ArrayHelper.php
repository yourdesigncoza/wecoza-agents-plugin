<?php
/**
 * Array Helper
 *
 * Provides utility methods for array manipulation.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

namespace WeCoza\Agents\Helpers;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Array Helper class
 *
 * @since 1.0.0
 */
class ArrayHelper {

    /**
     * Get value from array using dot notation
     *
     * @since 1.0.0
     * @param array $array Source array
     * @param string $key Dot notation key (e.g., 'user.profile.name')
     * @param mixed $default Default value if key not found
     * @return mixed Value or default
     */
    public static function get($array, $key, $default = null) {
        if (!is_array($array)) {
            return $default;
        }
        
        if (isset($array[$key])) {
            return $array[$key];
        }
        
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            
            $array = $array[$segment];
        }
        
        return $array;
    }

    /**
     * Set value in array using dot notation
     *
     * @since 1.0.0
     * @param array &$array Target array (passed by reference)
     * @param string $key Dot notation key
     * @param mixed $value Value to set
     * @return array Modified array
     */
    public static function set(&$array, $key, $value) {
        if (!is_array($array)) {
            $array = array();
        }
        
        $keys = explode('.', $key);
        $current = &$array;
        
        foreach ($keys as $i => $key) {
            if (count($keys) === 1) {
                break;
            }
            
            unset($keys[$i]);
            
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = array();
            }
            
            $current = &$current[$key];
        }
        
        $current[array_shift($keys)] = $value;
        
        return $array;
    }

    /**
     * Check if key exists in array using dot notation
     *
     * @since 1.0.0
     * @param array $array Source array
     * @param string $key Dot notation key
     * @return bool Whether key exists
     */
    public static function has($array, $key) {
        if (!is_array($array)) {
            return false;
        }
        
        if (array_key_exists($key, $array)) {
            return true;
        }
        
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }
            
            $array = $array[$segment];
        }
        
        return true;
    }

    /**
     * Remove value from array using dot notation
     *
     * @since 1.0.0
     * @param array &$array Target array (passed by reference)
     * @param string $key Dot notation key
     * @return void
     */
    public static function forget(&$array, $key) {
        if (!is_array($array)) {
            return;
        }
        
        $keys = explode('.', $key);
        $current = &$array;
        
        for ($i = 0; $i < count($keys) - 1; $i++) {
            $key = $keys[$i];
            
            if (!isset($current[$key]) || !is_array($current[$key])) {
                return;
            }
            
            $current = &$current[$key];
        }
        
        unset($current[array_pop($keys)]);
    }

    /**
     * Flatten a multi-dimensional array
     *
     * @since 1.0.0
     * @param array $array Source array
     * @param string $prepend Key prefix
     * @return array Flattened array
     */
    public static function flatten($array, $prepend = '') {
        $results = array();
        
        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, self::flatten($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }
        
        return $results;
    }

    /**
     * Only include specified keys from array
     *
     * @since 1.0.0
     * @param array $array Source array
     * @param array $keys Keys to include
     * @return array Filtered array
     */
    public static function only($array, $keys) {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Exclude specified keys from array
     *
     * @since 1.0.0
     * @param array $array Source array
     * @param array $keys Keys to exclude
     * @return array Filtered array
     */
    public static function except($array, $keys) {
        return array_diff_key($array, array_flip((array) $keys));
    }

    /**
     * Get first element of array
     *
     * @since 1.0.0
     * @param array $array Source array
     * @param callable|null $callback Optional callback to filter
     * @param mixed $default Default value
     * @return mixed First element or default
     */
    public static function first($array, $callback = null, $default = null) {
        if (is_null($callback)) {
            if (empty($array)) {
                return $default;
            }
            
            foreach ($array as $item) {
                return $item;
            }
        }
        
        foreach ($array as $key => $value) {
            if (call_user_func($callback, $value, $key)) {
                return $value;
            }
        }
        
        return $default;
    }

    /**
     * Get last element of array
     *
     * @since 1.0.0
     * @param array $array Source array
     * @param callable|null $callback Optional callback to filter
     * @param mixed $default Default value
     * @return mixed Last element or default
     */
    public static function last($array, $callback = null, $default = null) {
        if (is_null($callback)) {
            return empty($array) ? $default : end($array);
        }
        
        return self::first(array_reverse($array, true), $callback, $default);
    }

    /**
     * Filter array using callback
     *
     * @since 1.0.0
     * @param array $array Source array
     * @param callable $callback Filter callback
     * @return array Filtered array
     */
    public static function where($array, $callback) {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Check if any element passes truth test
     *
     * @since 1.0.0
     * @param array $array Source array
     * @param callable $callback Test callback
     * @return bool Whether any element passes
     */
    public static function some($array, $callback) {
        foreach ($array as $key => $value) {
            if (call_user_func($callback, $value, $key)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if all elements pass truth test
     *
     * @since 1.0.0
     * @param array $array Source array
     * @param callable $callback Test callback
     * @return bool Whether all elements pass
     */
    public static function every($array, $callback) {
        foreach ($array as $key => $value) {
            if (!call_user_func($callback, $value, $key)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Map array values
     *
     * @since 1.0.0
     * @param array $array Source array
     * @param callable $callback Map callback
     * @return array Mapped array
     */
    public static function map($array, $callback) {
        $keys = array_keys($array);
        $items = array_map($callback, $array, $keys);
        
        return array_combine($keys, $items);
    }

    /**
     * Pluck values from array of arrays
     *
     * @since 1.0.0
     * @param array $array Source array
     * @param string $value Value key
     * @param string|null $key Optional key for result array
     * @return array Plucked values
     */
    public static function pluck($array, $value, $key = null) {
        $results = array();
        
        foreach ($array as $item) {
            $itemValue = is_object($item) ? $item->{$value} : $item[$value];
            
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = is_object($item) ? $item->{$key} : $item[$key];
                $results[$itemKey] = $itemValue;
            }
        }
        
        return $results;
    }

    /**
     * Sort array by callback
     *
     * @since 1.0.0
     * @param array $array Source array
     * @param callable|string $callback Sort callback or key
     * @param string $direction Sort direction (asc/desc)
     * @return array Sorted array
     */
    public static function sort($array, $callback, $direction = 'asc') {
        if (is_string($callback)) {
            $key = $callback;
            $callback = function ($a, $b) use ($key, $direction) {
                $aVal = is_object($a) ? $a->{$key} : $a[$key];
                $bVal = is_object($b) ? $b->{$key} : $b[$key];
                
                if ($aVal == $bVal) {
                    return 0;
                }
                
                $result = $aVal < $bVal ? -1 : 1;
                
                return $direction === 'desc' ? -$result : $result;
            };
        }
        
        uasort($array, $callback);
        
        return $array;
    }

    /**
     * Group array by key
     *
     * @since 1.0.0
     * @param array $array Source array
     * @param string|callable $groupBy Group key or callback
     * @return array Grouped array
     */
    public static function groupBy($array, $groupBy) {
        $result = array();
        
        foreach ($array as $key => $value) {
            if (is_callable($groupBy)) {
                $groupKey = call_user_func($groupBy, $value, $key);
            } else {
                $groupKey = is_object($value) ? $value->{$groupBy} : $value[$groupBy];
            }
            
            if (!isset($result[$groupKey])) {
                $result[$groupKey] = array();
            }
            
            $result[$groupKey][] = $value;
        }
        
        return $result;
    }

    /**
     * Recursively merge arrays
     *
     * @since 1.0.0
     * @param array $array1 First array
     * @param array $array2 Second array
     * @return array Merged array
     */
    public static function merge($array1, $array2) {
        $merged = $array1;
        
        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::merge($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }
        
        return $merged;
    }

    /**
     * Convert array to HTML attributes
     *
     * @since 1.0.0
     * @param array $attributes Attributes array
     * @return string HTML attributes string
     */
    public static function toHtmlAttributes($attributes) {
        $html = array();
        
        foreach ($attributes as $key => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html[] = esc_attr($key);
                }
            } elseif (!is_null($value)) {
                $html[] = esc_attr($key) . '="' . esc_attr($value) . '"';
            }
        }
        
        return implode(' ', $html);
    }

    /**
     * Convert array to CSS classes
     *
     * @since 1.0.0
     * @param array $classes Classes array
     * @return string CSS classes string
     */
    public static function toCssClasses($classes) {
        $classList = array();
        
        foreach ($classes as $class => $condition) {
            if (is_numeric($class)) {
                $classList[] = $condition;
            } elseif ($condition) {
                $classList[] = $class;
            }
        }
        
        return implode(' ', array_unique(array_filter($classList)));
    }
}