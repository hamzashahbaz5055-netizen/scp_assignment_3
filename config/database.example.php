<?php
// Copy this file to database.php and enter your local or hosting credentials.
$host = getenv('SCP_DB_HOST') ?: '127.0.0.1';
$dbname = getenv('SCP_DB_NAME') ?: 'scp_database';
$username = getenv('SCP_DB_USER') ?: 'YOUR_DATABASE_USER';
$password = getenv('SCP_DB_PASSWORD') ?: 'YOUR_DATABASE_PASSWORD';

return new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
