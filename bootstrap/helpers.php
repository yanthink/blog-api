<?php

if (!function_exists('friendly_numbers')) {
    function friendly_numbers($n, $p = 1)
    {
        $v = pow(10, $p);

        if ($n >= 1000) {
            return intval($n * $v / 1000) / $v.'k';
        }

        return (string)$n;
    }
}