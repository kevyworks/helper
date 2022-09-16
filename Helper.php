<?php

class Helper
{
    /**
     * String Template
     *
     * @param string $string
     * @param array $haystack
     * @param string $prefix
     * @param boolean $remove_unmatched
     * @return string
     */
    public static function str_tpl($string, $haystack, $prefix = '%', $remove_unmatched = true) {
        if (! empty($haystack)) {
            $keys = array_map(function ($key) use ($prefix) {
                return $prefix . $key;
            }, array_keys($haystack));
            $string = str_replace($keys, array_values($haystack), $string);
        }

        // Some keys might still exist, and follows the pattern. If remove_unmatched is true, we remove those.
        if ($remove_unmatched) {
            $string = preg_replace('/' . $prefix . '(?:[^' . $prefix . ']\S\w+)/', '', $string);
        }

        return $string;
    }

    /**
     * The function returns {@see true} if the passed $haystack starts from the
     * $needle string or {@see false} otherwise.
     *
     * Polyfills Php 8.0 str_ends_with
     *
     * Accepts multiple needle, returns {@see true} directly if exists.
     *
     * @param string $haystack
     * @param string|array $needle
     * @return bool
     */
    public static function str_starts_with($haystack, $needle)
    {
        for ($i = 0; $c = count($needle = is_array($needle) ? $needle : [$needle]), $i < $c; $i++) {
            if (0 === substr_compare($haystack, $needle[$i], 0, strlen($needle[$i]))) {
                return true;
            }
        }

        return false;
    }

    /**
     * The function returns {@see true} if the passed $haystack ends with the
     * $needle string or {@see false} otherwise.
     *
     * Polyfills Php 8.0 str_ends_with
     *
     * Accepts multiple needle, returns {@see true} directly if exists.
     *
     * @param string $haystack
     * @param string|array $needle
     * @return bool
     */
    public static function str_ends_with($haystack, $needle)
    {
        for ($i = 0; $c = count($needle = is_array($needle) ? $needle : [$needle]), $i < $c; $i++) {
            if (0 === substr_compare($haystack, $needle[$i], -strlen($needle[$i]), null)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Dump and Die
     *
     * @param mixed ...$args
     * @return void
     */
    public static function dd(...$args)
    {
        self::dump(...$args);
        die(1);
    }

    /**
     * Dump the passed variables and end the script.
     *
     * @param mixed ...$args
     * @return void
     */
    public static function dump(...$args)
    {
        echo "<pre><code>";
        foreach ($args as $x) {
            print_r($x);
        }
        echo "</pre></code>";
    }

    /**
     * Check a string if it's a JSON.
     *
     * @param string $content
     * @param mixed $default
     * @return mixed|bool
     */
    public static function parse_json($content, $default = null) {
        $decoded = json_decode($content);

        // Check if Closure or a callable
        if ($default instanceof Closure) {
            $default = $default($decoded, $content);
        } elseif (is_callable($default)) {
            $default = $default($decoded);
        }

        return ((is_object($decoded) || is_array($decoded)) && (json_last_error() === JSON_ERROR_NONE))
            ? $decoded
            : $default;
    }

    /**
     * Check if associative array.
     *
     * @param array $arr
     * @return bool
     */
    public static function is_assoc($arr) {
        return ! empty($arr) && is_array($arr) && (array_keys($arr) !== range(0, count($arr) - 1));
    }

    /**
     * Create Query with DB prepare.
     *
     * Example: SELECT * FROM table WHERE field = ? AND id IN (?)
     *
     * @param string $statement
     * @param ...$params
     * @return string
     */
    public static function db_prepare($statement, ...$params)
    {
        if (count($params) === 1 && is_array($params[0])) {
            $params = $params[0];
        }

        $escape = function ($string) {
            $patterns     =	['/\x27\x22\x5C/u', '/\x0A/u', '/\x0D/u', '/\x00/u', '/\x1A/u'];
            $replacements =	['\\\$0', '\n', '\r', '\0', '\Z'];

            return '"'.preg_replace($patterns, $replacements, $string).'"';
        };

        $prefix = ':';

        // Replace key
        $keys = array_keys($params);
        $values = array_map(function ($val) use ($escape, $prefix) {
            if (is_array($val)) {
                return implode(',', array_map($escape, array_values($val)));
            }

            return $escape($val);
        }, array_values($params));

        // File for ? in statement
        if (strpos($statement, ' ? ') || strpos($statement, '=? ')) {
            foreach ($keys as $key) {
                $statement = preg_replace('/\?/', $prefix . $key, $statement, 1);
            }
        }

        return self::str_tpl($statement, array_combine($keys, $values), $prefix, false);
    }

    /**
     * Get Admin
     *
     * @param int|string $id_email
     * @param boolean $assoc
     * @param mixed $db_conn
     * @param mixed $db_instance
     * @return null|array|object
     * @throws Exception
     */
    public static function get_admin($id_email, $assoc = false, $db_conn = null, $db_instance = null)
    {
        if (is_null($db_instance) && ! class_exists('Database')) {
            throw new Exception('Database class does not exists.');
        }

        $db_conn = ! $db_conn ? $db_instance : $db_conn;

        if (! isset($id_email)) {
            return null;
        }

        $sql = self::db_prepare('SELECT * FROM `tbl_admin` WHERE `id` = ? OR `email` = ?', $id_email);

        return $db_conn->query($sql)->find($assoc ? 'assoc' : 'object');
    }
}