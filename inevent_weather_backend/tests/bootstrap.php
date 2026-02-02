<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Set test env defaults so tests don't depend on .env
$_ENV['JWT_SECRET'] = $_ENV['JWT_SECRET'] ?? 'test_secret_key_for_unit_tests';
$_ENV['JWT_EXPIRATION'] = $_ENV['JWT_EXPIRATION'] ?? 3600;
$_ENV['OPENWEATHER_API_KEY'] = $_ENV['OPENWEATHER_API_KEY'] ?? 'test_api_key';
