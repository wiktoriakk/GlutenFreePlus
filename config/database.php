<?php

return [
    'host' => getenv('DB_HOST') ?: 'db',
    'port' => getenv('DB_PORT') ?: '5432',
    'database' => getenv('DB_NAME') ?: 'glutenfree_db',
    'username' => getenv('DB_USER') ?: 'postgres',
    'password' => getenv('DB_PASSWORD') ?: 'postgres',
];