<?php

if (!function_exists('pr')) {
    function pr($var = '')
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }
}

if (!function_exists('unique_token')) {
    function unique_token($str = '')
    {
        return md5(uniqid($str, true));
    }
}
