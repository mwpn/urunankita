<?php

if (! function_exists('app_env')) {
    function app_env(string $key, mixed $default = null)
    {
        return env($key, $default);
    }
}


