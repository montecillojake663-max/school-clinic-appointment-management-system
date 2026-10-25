
<?php
session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

$error = $_GET["error"] ?? "";
$success = isset($_GET["success"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | SCAMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #3b82f6;
            --dark: #111827;
            --muted: #64748b;
        }

        * { box-sizing: border-box; }

        body {
            display: grid;
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            place-items: center;
            background:
                radial-gradient(circle at top right, rgba(59,130,246,.35), transparent 35%),
                linear-gradient(135deg, #0f172a, #1e3a5f);
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .register-card {
            width: 100%;
            max-width: 430px;
            padding: 38px;
            border: 1px solid rgba(255,255,255,.7);
            border-radius: 24px;
            background: rgba(255,255,255,.97);
            box-shadow: 0 30px 70px rgba(0,0,0,.28);
        }

        h1 {
            margin-bottom: 6px;
            color: var(--dark);
            font-size: 2rem;
            font-weight: 750;
            letter-spacing: -.8px;
        }

        .subtitle {
            margin-bottom: 25px;
            color: var(--muted);
        }

        .form-label {
            color: #475569;
            font-size: .88rem;
            font-weight: 650;
        }

        .form-control {
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 11px;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59,130,246,.12);
        }

        .register-button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 11px;
            color: white;
            background: linear-gradient(135deg, #3b82f6, #4f46e5);
            font-weight: 700;
        }

        .register-button:hover {
            color: white;
            background: linear-gradient(135deg, #2563eb, #4338ca);
        }

        .system-name {
            margin-top: 25px;
            color: #94a3b8;
            font-size: .75rem;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="register-card">
        <h1>Create account</h1>
        <p class="subtitle">Register to access the school clinic system.</p>

        <?php if ($error === "exists"): ?>
            <div class="alert alert-warning">That username is already taken.</div>
        <?php elseif ($error === "invalid"): ?>
            <div class="alert alert-warning">Please check your information and try again.</div>
        <?php elseif ($error === "failed"): ?>
            <div class="alert alert-danger">Registration failed. Please try again.</div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">Account created! You can now sign in.</div>
        <?php endif; ?>

        <form action="backend/register_code.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control"
                       placeholder="Enter your full name" maxlength="100" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control"
                       placeholder="Choose a username" maxlength="50"
                       autocomplete="username" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="Create a password" minlength="8"
                       autocomplete="new-password" required>
            </div>

            <button type="submit" class="btn register-button">Create Account</button>
        </form>

        <p class="text-center mt-3 mb-0">
            Already have an account? <a href="index.php">Sign in</a>
        </p>

        <p class="system-name">School Clinic Appointment Management System</p>
    </div>
</body>
</html>
