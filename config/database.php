<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'drabya');
define('DB_PASS', '#u}I#]!jBEQ%');
define('DB_NAME', 'drabya');

function getDBConnection()
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

function initializeDatabase()
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $conn->query($sql);
    $conn->close();
}
?>