<?php
declare(strict_types=1);
function database_config(): array
{
    $required = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'];
    $config = [];
    foreach ($required as $key) {
        $value = getenv($key);
        if ($value === false || $value === '') throw new RuntimeException("Missing required environment variable: {$key}");
        $config[$key] = $value;
    }
    return $config;
}
