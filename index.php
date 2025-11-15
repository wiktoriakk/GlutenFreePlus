<?php

require 'Routing.php';

$path = trim($_SERVER['REQUEST_URI'], '/');
$path = parse_url($path, PHP_URL_PATH);
$path = trim($path, '/');

//var_dump($path);

Routing::run($path);
?>