<?php

if (! function_exists('auth_user')) {
    function auth_user(): ?array
    {
        $session = session();
        return $session->get('auth_user');
    }
}

if (! function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return auth_user() !== null;
    }
}


