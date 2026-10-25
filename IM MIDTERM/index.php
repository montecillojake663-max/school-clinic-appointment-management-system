<?php

session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

$error = $_GET["error"] ?? "";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | SCAMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --dark: #111827;
            --muted: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            display: grid;
            min-height: 100vh;
            margin: 0;
            place-items: center;
            padding: 24px;

            background:
                radial-gradient(
                    circle at top right,
                    rgba(59, 130, 246, 0.35),
                    transparent 35%
                ),
                linear-gradient(135deg, #0f172a, #1e3a5f);

            font-family: "Segoe UI", Arial, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 430px;
            padding: 38px;

            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.97);

            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.28);
        }

        .logo-icon {
            display: grid;
            width: 58px;
            height: 58px;
            margin-bottom: 22px;
            place-items: center;

            border-radius: 17px;
            color: white;
            background: linear-gradient(135deg, #3b82f6, #4f46e5);
            box-shadow: 0 12px 25px rgba(59, 130, 246, 0.3);
        }

        .logo-icon svg {
            width: 29px;
            height: 29px;
        }

        h1 {
            margin-bottom: 6px;
            color: var(--dark);
            font-size: 2rem;
            font-weight: 750;
            letter-spacing: -0.8px;
        }

        .subtitle {
            margin-bottom: 28px;
            color: var(--muted);
        }

        .form-label {
            color: #475569;
            font-size: 0.88rem;
            font-weight: 650;
        }

        .form-control {
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 11px;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }

        .login-button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 11px;

            color: white;
            background: linear-gradient(
                135deg,
                var(--primary),
                #4f46e5
            );

            font-weight: 700;
            box-shadow: 0 10px 22px rgba(59, 130, 246, 0.25);
        }

        .login-button:hover {
            color: white;
            background: linear-gradient(
                135deg,
                var(--primary-dark),
                #4338ca
            );
        }

        .system-name {
            margin-top: 25px;
            color: #94a3b8;
            font-size: 0.75rem;
            text-align: center;
        }

        .alert {
            border: none;
            border-radius: 11px;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>

    <div class="login-card">

        <div class="logo-icon">
            <i data-lucide="heart-pulse"></i>
        </div>

        <h1>Welcome back</h1>

        <p class="subtitle">
            Sign in to manage the school clinic.
        </p>

        <?php if ($error === "invalid"): ?>

            <div class="alert alert-danger">
                Incorrect username or password.
            </div>

        <?php elseif ($error === "missing"): ?>

            <div class="alert alert-warning">
                Please enter your username and password.
            </div>

        <?php endif; ?>

        <form
            action="backend/login_code.php"
            method="POST"
        >

            <div class="mb-3">

                <label class="form-label">
                    Username
                </label>

                <input
                    type="text"
                    name="username"
                    class="form-control"
                    placeholder="Enter your username"
                    autocomplete="username"
                    required
                >

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >

            </div>

            <button
                type="submit"
                class="btn login-button"
            >
                Sign In
            </button>


        </form>

        <div class="text-center mt-3">
            <span class="text-muted">Don't have an account?</span>
            <a href="register.php" class="btn btn-outline-primary w-100 mt-2">
                Create Account
            </a>
        </div>

        <p class="system-name">

            School Clinic Appointment Management System
        </p>

    </div>

    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        lucide.createIcons();
    </script>

</body>

</html>