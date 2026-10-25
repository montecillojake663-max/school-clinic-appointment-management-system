<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/connection.php";

$totalStudents = (int) $conn
    ->query("SELECT COUNT(*) FROM students_table")
    ->fetchColumn();

$todayAppointments = (int) $conn
    ->query("
        SELECT COUNT(*)
        FROM appointments_table AS appointments
        INNER JOIN time_slots_table AS slots
            ON appointments.slot_id = slots.slot_id
        WHERE slots.slot_date = CURDATE()
    ")
    ->fetchColumn();

$scheduledAppointments = (int) $conn
    ->query("
        SELECT COUNT(*)
        FROM appointments_table
        WHERE status = 'Scheduled'
    ")
    ->fetchColumn();

$totalVisits = (int) $conn
    ->query("
        SELECT COUNT(*)
        FROM visit_records_table
    ")
    ->fetchColumn();

$statement = $conn->prepare("
    SELECT
        appointments.appointment_id,
        students.first_name,
        students.last_name,
        slots.start_time,
        appointments.reason,
        appointments.status

    FROM appointments_table AS appointments

    INNER JOIN students_table AS students
        ON appointments.student_id = students.student_id

    INNER JOIN time_slots_table AS slots
        ON appointments.slot_id = slots.slot_id

    WHERE slots.slot_date = CURDATE()

    ORDER BY slots.start_time ASC
");

$statement->execute();
$appointmentsToday = $statement->fetchAll();

function e(?string $value): string
{
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard | SCAMS</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Sidebar design -->
    <link rel="stylesheet" href="includes/sidebar.css">

    <!-- Shared website design -->
    <link rel="stylesheet" href="includes/app.css">

    <!-- Dashboard design -->
    <link rel="stylesheet" href="includes/dashboard.css">
</head>

<body>

    <?php require_once __DIR__ . "/includes/sidebar.php"; ?>

    <main class="main-content">

        <!-- Dashboard heading -->

        <header class="dashboard-header">

            <div>
                <h1>Dashboard</h1>

                <p>
                    Here is today's school clinic overview.
                </p>
            </div>

            <div class="user-card">

                <div class="user-avatar">
                    <i data-lucide="user-round"></i>
                </div>

                <div class="user-details">

                    <strong>
                        <?= e($_SESSION["full_name"] ?? "Clinic User"); ?>
                    </strong>

                    <span>
                        <?= e($_SESSION["role"] ?? "Clinic Staff"); ?>
                    </span>

                </div>

            </div>

        </header>

        <!-- Statistic cards -->

        <section class="row g-4">

            <!-- Students -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-top">

                        <span>Students</span>

                        <div class="stat-icon icon-blue">
                            <i data-lucide="users"></i>
                        </div>

                    </div>

                    <h2><?= $totalStudents; ?></h2>

                    <p>
                        Total registered students
                    </p>

                </div>

            </div>

            <!-- Today's appointments -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-top">

                        <span>Today</span>

                        <div class="stat-icon icon-purple">
                            <i data-lucide="calendar-days"></i>
                        </div>

                    </div>

                    <h2><?= $todayAppointments; ?></h2>

                    <p>
                        Appointments today
                    </p>

                </div>

            </div>

            <!-- Scheduled appointments -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-top">

                        <span>Scheduled</span>

                        <div class="stat-icon icon-orange">
                            <i data-lucide="clock-3"></i>
                        </div>

                    </div>

                    <h2><?= $scheduledAppointments; ?></h2>

                    <p>
                        Upcoming appointments
                    </p>

                </div>

            </div>

            <!-- Visit records -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-top">

                        <span>Visits</span>

                        <div class="stat-icon icon-green">
                            <i data-lucide="clipboard-check"></i>
                        </div>

                    </div>

                    <h2><?= $totalVisits; ?></h2>

                    <p>
                        Recorded clinic visits
                    </p>

                </div>

            </div>

        </section>

        <!-- Today's appointments table -->

        <section class="content-card">

            <div class="content-card-header">

                <div>
                    <h2>Today's Appointments</h2>

                    <p>
                        Students scheduled to visit the clinic today.
                    </p>
                </div>

                <a href="appointments.php">
                    View All
                </a>

            </div>

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>

                        <tr>
                            <th>Student</th>
                            <th>Time</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if (empty($appointmentsToday)): ?>

                            <tr>
                                <td
                                    colspan="4"
                                    class="empty-state"
                                >
                                    No appointments scheduled for today.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($appointmentsToday as $appointment): ?>

                                <?php
                                $statusClass = strtolower(
                                    $appointment["status"]
                                );
                                ?>

                                <tr>

                                    <td>

                                        <div class="student-cell">

                                            <div class="student-avatar">
                                                <?= strtoupper(
                                                    substr(
                                                        $appointment["first_name"],
                                                        0,
                                                        1
                                                    )
                                                ); ?>
                                            </div>

                                            <strong>
                                                <?= e(
                                                    $appointment["first_name"] .
                                                    " " .
                                                    $appointment["last_name"]
                                                ); ?>
                                            </strong>

                                        </div>

                                    </td>

                                    <td>
                                        <?= date(
                                            "g:i A",
                                            strtotime(
                                                $appointment["start_time"]
                                            )
                                        ); ?>
                                    </td>

                                    <td>
                                        <?= e($appointment["reason"]); ?>
                                    </td>

                                    <td>

                                        <span
                                            class="dashboard-status status-<?= e($statusClass); ?>"
                                        >
                                            <?= e($appointment["status"]); ?>
                                        </span>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

    <!-- Activate Lucide icons -->

    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        lucide.createIcons();
    </script>

</body>

</html>