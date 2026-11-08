<?php
// Local configuration is ignored by Git. Replace credentials for deployment.
$host = '127.0.0.1';
$dbname = 'scp_database';
$username = 'root';
$password = '12345';

return new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
