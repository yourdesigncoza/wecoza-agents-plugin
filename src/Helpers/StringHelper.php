<?php
/**
 * String Helper
 *
 * Provides utility methods for string manipulation.
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
 * String Helper class
 *
 * @since 1.0.0
 */
class StringHelper {

    /**
     * Convert string to camelCase
     *
     * @since 1.0.0
     * @param string $string Input string
     * @return string Camel case string
     */
    public static function camelCase($string) {
        $string = str_replace(array('-', '_'), ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);
        return lcfirst($string);
    }

    /**
     * Convert string to StudlyCase
     *
     * @since 1.0.0
     * @param string $string Input string
     * @return string Studly case string
     */
    public static function studlyCase($string) {
        $string = str_replace(array('-', '_'), ' ', $string);
        $string = ucwords($string);
        return str_replace(' ', '', $string);
    }

    /**
     * Convert string to snake_case
     *
     * @since 1.0.0
     * @param string $string Input string
     * @return string Snake case string
     */
    public static function snakeCase($string) {
        $string = preg_replace('/\s+/u', '', $string);
        $string = preg_replace('/(.)(?=[A-Z])/u', '$1_', $string);
        return strtolower($string);
    }

    /**
     * Convert string to kebab-case
     *
     * @since 1.0.0
     * @param string $string Input string
     * @return string Kebab case string
     */
    public static function kebabCase($string) {
        return str_replace('_', '-', self::snakeCase($string));
    }

    /**
     * Convert string to Title Case
     *
     * @since 1.0.0
     * @param string $string Input string
     * @return string Title case string
     */
    public static function titleCase($string) {
        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Check if string starts with substring
     *
     * @since 1.0.0
     * @param string $haystack String to search in
     * @param string|array $needles String(s) to search for
     * @return bool Whether string starts with needle
     */
    public static function startsWith($haystack, $needles) {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if string ends with substring
     *
     * @since 1.0.0
     * @param string $haystack String to search in
     * @param string|array $needles String(s) to search for
     * @return bool Whether string ends with needle
     */
    public static function endsWith($haystack, $needles) {
        foreach ((array) $needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string) $needle) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if string contains substring
     *
     * @since 1.0.0
     * @param string $haystack String to search in
     * @param string|array $needles String(s) to search for
     * @return bool Whether string contains needle
     */
    public static function contains($haystack, $needles) {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Limit string length
     *
     * @since 1.0.0
     * @param string $string Input string
     * @param int $limit Character limit
     * @param string $end Ending string
     * @return string Limited string
     */
    public static function limit($string, $limit = 100, $end = '...') {
        if (mb_strlen($string, 'UTF-8') <= $limit) {
            return $string;
        }
        
        return rtrim(mb_substr($string, 0, $limit, 'UTF-8')) . $end;
    }

    /**
     * Limit string by words
     *
     * @since 1.0.0
     * @param string $string Input string
     * @param int $words Word limit
     * @param string $end Ending string
     * @return string Limited string
     */
    public static function words($string, $words = 100, $end = '...') {
        $string = trim($string);
        $wordArray = explode(' ', $string);
        
        if (count($wordArray) <= $words) {
            return $string;
        }
        
        return implode(' ', array_slice($wordArray, 0, $words)) . $end;
    }

    /**
     * Generate random string
     *
     * @since 1.0.0
     * @param int $length String length
     * @param string $type Type of characters (alpha, numeric, alnum, special)
     * @return string Random string
     */
    public static function random($length = 16, $type = 'alnum') {
        $pools = array(
            'alpha' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'numeric' => '0123456789',
            'alnum' => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'special' => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=[]{}|;:,.<>?',
        );
        
        $pool = isset($pools[$type]) ? $pools[$type] : $pools['alnum'];
        $poolLength = strlen($pool);
        $string = '';
        
        for ($i = 0; $i < $length; $i++) {
            $string .= $pool[random_int(0, $poolLength - 1)];
        }
        
        return $string;
    }

    /**
     * Generate slug from string
     *
     * @since 1.0.0
     * @param string $string Input string
     * @param string $separator Separator character
     * @return string Slug
     */
    public static function slug($string, $separator = '-') {
        // Convert to ASCII
        $string = remove_accents($string);
        
        // Replace non letter or digits by separator
        $string = preg_replace('~[^\pL\d]+~u', $separator, $string);
        
        // Transliterate
        $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
        
        // Remove unwanted characters
        $string = preg_replace('~[^-\w]+~', '', $string);
        
        // Trim
        $string = trim($string, $separator);
        
        // Remove duplicate separators
        $string = preg_replace('~-+~', $separator, $string);
        
        // Lowercase
        $string = strtolower($string);
        
        return empty($string) ? 'n-a' : $string;
    }

    /**
     * Replace first occurrence
     *
     * @since 1.0.0
     * @param string $search Search string
     * @param string $replace Replace string
     * @param string $subject Subject string
     * @return string Modified string
     */
    public static function replaceFirst($search, $replace, $subject) {
        if ($search == '') {
            return $subject;
        }
        
        $position = strpos($subject, $search);
        
        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }
        
        return $subject;
    }

    /**
     * Replace last occurrence
     *
     * @since 1.0.0
     * @param string $search Search string
     * @param string $replace Replace string
     * @param string $subject Subject string
     * @return string Modified string
     */
    public static function replaceLast($search, $replace, $subject) {
        $position = strrpos($subject, $search);
        
        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }
        
        return $subject;
    }

    /**
     * Parse class@method syntax
     *
     * @since 1.0.0
     * @param string $callback Callback string
     * @param string|null $default Default method
     * @return array Class and method
     */
    public static function parseCallback($callback, $default = null) {
        return static::contains($callback, '@') ? explode('@', $callback, 2) : array($callback, $default);
    }

    /**
     * Convert to boolean
     *
     * @since 1.0.0
     * @param mixed $value Value to convert
     * @return bool Boolean value
     */
    public static function toBoolean($value) {
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            $value = strtolower($value);
            return in_array($value, array('1', 'true', 'on', 'yes'));
        }
        
        return (bool) $value;
    }

    /**
     * Mask string
     *
     * @since 1.0.0
     * @param string $string String to mask
     * @param int $start Start position
     * @param int $length Length to mask
     * @param string $mask Mask character
     * @return string Masked string
     */
    public static function mask($string, $start, $length = null, $mask = '*') {
        $stringLength = mb_strlen($string);
        
        if ($start < 0) {
            $start = max(0, $stringLength + $start);
        }
        
        if ($length === null) {
            $length = $stringLength - $start;
        }
        
        $mask = str_repeat($mask, $length);
        
        return mb_substr($string, 0, $start) . $mask . mb_substr($string, $start + $length);
    }

    /**
     * Extract excerpt from content
     *
     * @since 1.0.0
     * @param string $text Text to extract from
     * @param int $length Excerpt length
     * @param string $more More string
     * @return string Excerpt
     */
    public static function excerpt($text, $length = 55, $more = '...') {
        $text = strip_shortcodes($text);
        $text = wp_strip_all_tags($text);
        $text = str_replace(']]>', ']]&gt;', $text);
        
        $words = preg_split("/[\n\r\t ]+/", $text, $length + 1, PREG_SPLIT_NO_EMPTY);
        
        if (count($words) > $length) {
            array_pop($words);
            $text = implode(' ', $words);
            $text = $text . $more;
        } else {
            $text = implode(' ', $words);
        }
        
        return $text;
    }

    /**
     * Highlight text
     *
     * @since 1.0.0
     * @param string $text Text to search in
     * @param string $phrase Phrase to highlight
     * @param string $format Format string with %s placeholder
     * @return string Text with highlights
     */
    public static function highlight($text, $phrase, $format = '<mark>%s</mark>') {
        if (empty($phrase)) {
            return $text;
        }
        
        $phrase = preg_quote($phrase, '/');
        
        return preg_replace_callback(
            '/(' . $phrase . ')/i',
            function ($matches) use ($format) {
                return sprintf($format, $matches[0]);
            },
            $text
        );
    }

    /**
     * Convert string to array
     *
     * @since 1.0.0
     * @param string $string Input string
     * @param string $delimiter Delimiter
     * @return array Array of values
     */
    public static function toArray($string, $delimiter = ',') {
        if (is_array($string)) {
            return $string;
        }
        
        if (empty($string)) {
            return array();
        }
        
        return array_map('trim', explode($delimiter, $string));
    }

    /**
     * Clean string for safe filename
     *
     * @since 1.0.0
     * @param string $string Input string
     * @param string $replacement Replacement for invalid chars
     * @return string Safe filename
     */
    public static function filename($string, $replacement = '-') {
        $string = remove_accents($string);
        $string = preg_replace('/[^a-zA-Z0-9._-]/', $replacement, $string);
        $string = preg_replace('/' . preg_quote($replacement) . '+/', $replacement, $string);
        $string = trim($string, $replacement);
        
        return empty($string) ? 'file' : $string;
    }

    /**
     * Format bytes to human readable
     *
     * @since 1.0.0
     * @param int $bytes Bytes
     * @param int $precision Decimal precision
     * @return string Formatted size
     */
    public static function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Pluralize string
     *
     * @since 1.0.0
     * @param int $count Count
     * @param string $singular Singular form
     * @param string $plural Plural form
     * @return string Pluralized string
     */
    public static function pluralize($count, $singular, $plural = null) {
        if ($plural === null) {
            $plural = $singular . 's';
        }
        
        return $count == 1 ? $singular : $plural;
    }

    /**
     * Normalize line endings
     *
     * @since 1.0.0
     * @param string $string Input string
     * @return string Normalized string
     */
    public static function normalizeLineEndings($string) {
        return str_replace(array("\r\n", "\r"), "\n", $string);
    }
}