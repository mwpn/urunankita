<?php

if (! function_exists('now_utc')) {
    function now_utc(): string
    {
        return gmdate('Y-m-d H:i:s');
    }
}


