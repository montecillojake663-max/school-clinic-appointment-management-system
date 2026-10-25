
<?php
require_once '../connection.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../register.php");
    exit;
}

$full_name = trim($_POST["full_name"] ?? "");
$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

if (
    $full_name === "" ||
    $username === "" ||
    strlen($password) < 8 ||
    strlen($full_name) > 100 ||
    strlen($username) > 50
) {
    header("Location: ../register.php?error=invalid");
    exit;
}

try {
    // Check whether the username already exists.
    $check = $conn->prepare(
        "SELECT user_id FROM users_table WHERE username = ? LIMIT 1"
    );
    $check->execute([$username]);

    if ($check->fetch()) {
        header("Location: ../register.php?error=exists");
        exit;
    }

    // Hash the password before saving it.
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Public registration always creates a regular user account.
    $stmt = $conn->prepare(
        "INSERT INTO users_table (full_name, role, username, password)
         VALUES (?, 'user', ?, ?)"
    );

    $stmt->execute([$full_name, $username, $hashed_password]);

    header("Location: ../register.php?success=1");
    exit;

} catch (PDOException $error) {
    error_log("Registration error: " . $error->getMessage());
    header("Location: ../register.php?error=failed");
    exit;
}
