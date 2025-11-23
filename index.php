<?php
session_start();

require_once 'Routing.php';

$path = trim($_SERVER['REQUEST_URI'], '/');
$path = parse_url($path, PHP_URL_PATH);
$path = trim($path, '/');

Routing::run($path);