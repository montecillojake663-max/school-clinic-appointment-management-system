
<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "scams_db";

try {
    $conn = new PDO(
        "mysql:host=$servername;dbname=$database;charset=utf8mb4",
        $username,
        $password
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $error) {
    error_log($error->getMessage());
    die("Database connection failed. Please check your database configuration.");
}
