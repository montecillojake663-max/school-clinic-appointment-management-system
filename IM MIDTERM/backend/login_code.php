<?php

session_start();

require_once __DIR__ . "/../connection.php";
require_once __DIR__ . "/../classes/user.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

if ($username === "" || $password === "") {
    header("Location: ../index.php?error=missing");
    exit;
}

$userObject = new User($conn);
$user = $userObject->findByUsername($username);

if (!$user || !password_verify($password, $user["password"])) {
    header("Location: ../index.php?error=invalid");
    exit;
}

session_regenerate_id(true);

$_SESSION["user_id"] = $user["user_id"];
$_SESSION["full_name"] = $user["full_name"];
$_SESSION["role"] = $user["role"];

header("Location: ../dashboard.php");
exit;