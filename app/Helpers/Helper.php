<?php

if (!function_exists('utf8_clean')) {
    function utf8_clean($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        // Remove invalid bytes
        $value = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $value);

        // Convert to valid UTF-8
        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
}

if (!function_exists('pre')) {
    function pre($arr, $e = 0)
    {
        echo "<pre>";
        print_r($arr);
        echo "</pre>";
        if ($e == 1) {
            exit;
        }
    }
}

if (!function_exists('br')) {
    function br()
    {
        echo "</br>";
    }
}


if (! function_exists('replace_placeholders')) {
    function replace_placeholders(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', (string) $value, $template);
        }

        return $template;
    }
}
