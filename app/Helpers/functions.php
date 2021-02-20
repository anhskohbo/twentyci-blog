<?php

use Illuminate\Support\Facades\Auth;

/**
 * Return current user based on current request.
 *
 * @return \App\Models\User|null
 */
function current_user()
{
    return Auth::user() ?: request()->user();
}

/**
 * Retrieves a modified URL query string.
 *
 * @param string|array $key Either a query variable key, or an associative array of query variables.
 * @param string $value Optional. Either a query variable value, or a URL to act upon.
 * @param string $url Optional. A URL to act upon.
 * @return string
 */
function add_query_arg(...$args)
{
    if (is_array($args[0])) {
        if (count($args) < 2 || false === $args[1]) {
            $uri = $_SERVER['REQUEST_URI'];
        } else {
            $uri = $args[1];
        }
    } elseif (count($args) < 3 || false === $args[2]) {
        $uri = $_SERVER['REQUEST_URI'];
    } else {
        $uri = $args[2];
    }

    $frag = strstr($uri, '#');
    if ($frag) {
        $uri = substr($uri, 0, -strlen($frag));
    } else {
        $frag = '';
    }

    if (0 === stripos($uri, 'http://')) {
        $protocol = 'http://';
        $uri = substr($uri, 7);
    } elseif (0 === stripos($uri, 'https://')) {
        $protocol = 'https://';
        $uri = substr($uri, 8);
    } else {
        $protocol = '';
    }

    if (strpos($uri, '?') !== false) {
        [$base, $query] = explode('?', $uri, 2);
        $base .= '?';
    } elseif ($protocol || strpos($uri, '=') === false) {
        $base = $uri . '?';
        $query = '';
    } else {
        $base = '';
        $query = $uri;
    }

    parse_str($query, $qs);
    $qs = map_deep($qs, 'urlencode'); // This re-URL-encodes things that were already in the query string.

    if (is_array($args[0])) {
        foreach ($args[0] as $k => $v) {
            $qs[$k] = $v;
        }
    } else {
        $qs[$args[0]] = $args[1];
    }

    foreach ($qs as $k => $v) {
        if (false === $v) {
            unset($qs[$k]);
        }
    }

    $ret = http_build_query($qs);
    $ret = trim($ret, '?');
    $ret = preg_replace('#=(&|$)#', '$1', $ret);
    $ret = $protocol . $base . $ret . $frag;
    $ret = rtrim($ret, '?');

    return $ret;
}

/**
 * @param array|object $value
 * @param callable $callback
 * @return array|false|mixed
 */
function map_deep($value, $callback)
{
    if (is_array($value)) {
        foreach ($value as $index => $item) {
            $value[$index] = map_deep($item, $callback);
        }
    } elseif (is_object($value)) {
        $object_vars = get_object_vars($value);
        foreach ($object_vars as $property_name => $property_value) {
            $value->$property_name = map_deep($property_value, $callback);
        }
    } else {
        $value = call_user_func($callback, $value);
    }

    return $value;
}

/**
 * Properly strip all HTML tags including script and style
 *
 * This differs from strip_tags() because it removes the contents of
 * the `<script>` and `<style>` tags. E.g. `strip_tags( '<script>something</script>' )`
 * will return 'something'. wp_strip_all_tags will return ''
 *
 * @param string $string String containing HTML tags
 * @param bool $remove_breaks Optional. Whether to remove left over line breaks and white space chars
 *
 * @return string The processed string.
 */
function strip_all_tags($string, $remove_breaks = false)
{
    $string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);

    $string = strip_tags($string);

    if ($remove_breaks) {
        $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
    }

    return trim($string);
}

/**
 * Sanitizes a string from user input or from the database.
 *
 * - Checks for invalid UTF-8,
 * - Converts single `<` characters to entities
 * - Strips all tags
 * - Removes line breaks, tabs, and extra whitespace
 * - Strips octets
 *
 * @param string $str String to sanitize.
 * @return string Sanitized string.
 */
function sanitize_text_field($str)
{
    return _sanitize_text_fields($str);
}

/**
 * Sanitizes a multiline string from user input or from the database.
 *
 * The function is like sanitize_text_field(), but preserves
 * new lines (\n) and other whitespace, which are legitimate
 * input in textarea elements.
 *
 * @param string $str String to sanitize.
 * @return string Sanitized string.
 */
function sanitize_textarea_field($str)
{
    return _sanitize_text_fields($str, true);
}

/**
 * Internal helper function to sanitize a string from user input or from the db.
 *
 * @param mixed $str String to sanitize.
 * @param bool $keep_newlines optional Whether to keep newlines. Default: false.
 * @return string Sanitized string.
 *
 * @access private
 */
function _sanitize_text_fields($str, $keep_newlines = false)
{
    if (is_object($str) || is_array($str)) {
        return '';
    }

    $str = (string)$str;

    $filtered = _check_invalid_utf8($str);

    if (strpos($filtered, '<') !== false) {
        $filtered = _pre_kses_less_than($filtered);

        // This will strip extra whitespace for us.
        $filtered = strip_all_tags($filtered);

        // Use html entities in a special case to make sure no later
        // newline stripping stage could lead to a functional tag
        $filtered = str_replace("<\n", "&lt;\n", $filtered);
    }

    if (!$keep_newlines) {
        $filtered = preg_replace('/[\r\n\t ]+/', ' ', $filtered);
    }

    $filtered = trim($filtered);

    $found = false;
    while (preg_match('/%[a-f0-9]{2}/i', $filtered, $match)) {
        $filtered = str_replace($match[0], '', $filtered);
        $found = true;
    }

    if ($found) {
        // Strip out the whitespace that may now exist after removing the octets.
        $filtered = trim(preg_replace('/ +/', ' ', $filtered));
    }

    return $filtered;
}

/**
 * Checks for invalid UTF8 in a string.
 *
 * @param string $string The text which is to be checked.
 * @param bool $strip Optional. Whether to attempt to strip out invalid UTF8. Default is false.
 * @return string The checked text.
 */
function _check_invalid_utf8($string, $strip = false)
{
    $string = (string)$string;

    if ('' === $string) {
        return '';
    }

    // Check for support for utf8 in the installed PCRE library once and store the result in a static,
    static $utf8_pcre = null;
    if (!isset($utf8_pcre)) {
        $utf8_pcre = @preg_match('/^./u', 'a');
    }

    // We can't demand utf8 in the PCRE installation, so just return the string in those cases,
    if (!$utf8_pcre) {
        return $string;
    }

    // preg_match fails when it encounters invalid UTF8 in $string.
    if (1 === @preg_match('/^./us', $string)) {
        return $string;
    }

    // Attempt to strip the bad chars if requested (not recommended).
    if ($strip && function_exists('iconv')) {
        return iconv('utf-8', 'utf-8', $string);
    }

    return '';
}

/**
 * Convert lone less than signs.
 *
 * KSES already converts lone greater than signs.
 *
 * @param string $text Text to be converted.
 * @return string Converted text.
 */
function _pre_kses_less_than($text)
{
    return preg_replace_callback(
        '%<[^>]*?((?=<)|>|$)%',
        function ($matches) {
            if (false === strpos($matches[0], '>')) {
                return e($matches[0]);
            }

            return $matches[0];
        },
        $text
    );
}
