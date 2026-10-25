
<?php
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/connection.php";

// Escape output before displaying database values.
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ""), ENT_QUOTES, "UTF-8");
}

// Read report filters.
$startDate = $_GET["start_date"] ?? "";
$endDate = $_GET["end_date"] ?? "";
$statusFilter = $_GET["status"] ?? "";

// Validate date values.
$isValidDate = function ($date): bool {
    if ($date === "") {
        return true;
    }

    $parsed = DateTime::createFromFormat("!Y-m-d", $date);

    return $parsed !== false && $parsed->format("Y-m-d") === $date;
};

if (!is_string($startDate) || !$isValidDate($startDate)) {
    $startDate = "";
}

if (!is_string($endDate) || !$isValidDate($endDate)) {
    $endDate = "";
}

if (!is_string($statusFilter)) {
    $statusFilter = "";
}

// Summary statistics from the existing database.
$totalStudents = (int) $conn
    ->query("SELECT COUNT(*) FROM students_table")
    ->fetchColumn();

$totalHealthProfiles = (int) $conn
    ->query("SELECT COUNT(*) FROM health_profiles_table")
    ->fetchColumn();

$totalAppointments = (int) $conn
    ->query("SELECT COUNT(*) FROM appointments_table")
    ->fetchColumn();

$availableSlots = (int) $conn
    ->query("SELECT COUNT(*) FROM time_slots_table WHERE is_available = 1")
    ->fetchColumn();

// Retrieve the actual appointment status values.
$statusStatement = $conn->query("
    SELECT DISTINCT status
    FROM appointments_table
    WHERE status IS NOT NULL AND status <> ''
    ORDER BY status ASC
");

$statuses = $statusStatement->fetchAll(PDO::FETCH_COLUMN);

// Only accept status values that exist in the database.
if ($statusFilter !== "" && !in_array($statusFilter, $statuses, true)) {
    $statusFilter = "";
}

// Build the appointment report with optional filters.
$sql = "
    SELECT
        appointments.appointment_id,
        appointments.reason,
        appointments.status,
        students.first_name,
        students.last_name,
        students.grade_level,
        students.section,
        users.full_name AS staff_name,
        slots.slot_date,
        slots.start_time,
        slots.end_time
    FROM appointments_table AS appointments
    INNER JOIN students_table AS students
        ON appointments.student_id = students.student_id
    INNER JOIN users_table AS users
        ON appointments.user_id = users.user_id
    INNER JOIN time_slots_table AS slots
        ON appointments.slot_id = slots.slot_id
    WHERE 1 = 1
";

$params = [];

if ($startDate !== "") {
    $sql .= " AND slots.slot_date >= ?";
    $params[] = $startDate;
}

if ($endDate !== "") {
    $sql .= " AND slots.slot_date <= ?";
    $params[] = $endDate;
}

if ($statusFilter !== "") {
    $sql .= " AND appointments.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY slots.slot_date DESC, slots.start_time DESC";

$statement = $conn->prepare($sql);
$statement->execute($params);
$appointments = $statement->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reports | SCAMS</title>

    <!-- Existing stylesheets -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="includes/app.css">
    <link rel="stylesheet" href="includes/dashboard.css">
    <link rel="stylesheet" href="includes/sidebar.css">

    <style>
        /* =========================================
           REPORTS PAGE
           Styling inspired by the Time Slots page.
           ========================================= */

        body {
            background: #f3f6fc;
        }

        .reports-page {
            width: 100%;
            max-width: 1600px;
            margin: 0 auto;
            padding: 30px 32px 40px;
            box-sizing: border-box;
        }

        /* Gradient page header */
        .reports-hero {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 18px;
            min-height: 134px;
            padding: 28px 32px;
            margin-bottom: 28px;
            border-radius: 22px;
            color: #fff;
            background: linear-gradient(
                115deg,
                #2863ed 0%,
                #4f46e5 100%
            );
            box-shadow: 0 16px 35px rgba(59, 130, 246, 0.18);
        }

        .reports-hero::after {
            content: "";
            position: absolute;
            width: 210px;
            height: 210px;
            right: -35px;
            bottom: -120px;
            border: 24px solid rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            pointer-events: none;
        }

        .reports-hero-icon {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 54px;
            width: 54px;
            height: 54px;
            border-radius: 16px;
            background: #fff;
            color: #2863ed;
        }

        .reports-hero-icon svg {
            width: 27px;
            height: 27px;
        }

        .reports-hero-text {
            position: relative;
            z-index: 1;
        }

        .reports-hero-text h1 {
            margin: 0 0 5px;
            color: #fff;
            font-size: 32px;
            font-weight: 750;
            letter-spacing: -0.8px;
        }

        .reports-hero-text p {
            margin: 0;
            color: rgba(255, 255, 255, 0.88);
            font-size: 15px;
        }

        .reports-print-btn {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 46px;
            padding: 12px 19px;
            margin-left: auto;
            border: 0;
            border-radius: 12px;
            background: #fff;
            color: #2863ed;
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .reports-print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 18px rgba(15, 23, 42, 0.13);
        }

        .reports-print-btn svg {
            width: 17px;
            height: 17px;
        }

        /* Statistic cards */
        .reports-stats {
            margin-bottom: 28px;
        }

        .reports-stat-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 14px;
            min-height: 112px;
            height: 100%;
            padding: 20px;
            border: 1px solid #e3eaf4;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 10px 28px rgba(31, 55, 90, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .reports-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(31, 55, 90, 0.08);
        }

        .reports-stat-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 44px;
            width: 44px;
            height: 44px;
            border-radius: 13px;
        }

        .reports-stat-icon svg {
            width: 22px;
            height: 22px;
        }

        .reports-stat-icon.blue {
            background: #dbeafe;
            color: #2563eb;
        }

        .reports-stat-icon.purple {
            background: #ede9fe;
            color: #7c3aed;
        }

        .reports-stat-icon.green {
            background: #dcfce7;
            color: #16a34a;
        }

        .reports-stat-icon.orange {
            background: #ffedd5;
            color: #ea580c;
        }

        .reports-stat-info {
            min-width: 0;
        }

        .reports-stat-label {
            margin: 0 0 5px;
            color: #64748b;
            font-size: 13px;
            font-weight: 500;
        }

        .reports-stat-number {
            margin: 0;
            color: #0f172a;
            font-size: 26px;
            font-weight: 750;
            line-height: 1.2;
        }

        /* Main report panel */
        .reports-card {
            overflow: hidden;
            border: 1px solid #e1e8f2;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 10px 28px rgba(31, 55, 90, 0.05);
        }

        .reports-card-header {
            padding: 22px;
            border-bottom: 1px solid #e9eef5;
        }

        .reports-card-header h2 {
            margin: 0 0 5px;
            color: #0f172a;
            font-size: 17px;
            font-weight: 750;
        }

        .reports-card-header p {
            margin: 0;
            color: #64748b;
            font-size: 13px;
        }

        .reports-card-body {
            padding: 22px;
        }

        /* Filter form */
        .reports-filter {
            display: flex;
            align-items: flex-end;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 24px;
            padding: 18px;
            border: 1px solid #e7edf5;
            border-radius: 14px;
            background: #f8fafc;
        }

        .reports-filter-group {
            display: flex;
            flex: 1 1 150px;
            flex-direction: column;
            gap: 7px;
            min-width: 135px;
        }

        .reports-filter-group label {
            color: #475569;
            font-size: 12px;
            font-weight: 700;
        }

        .reports-filter-group input,
        .reports-filter-group select {
            box-sizing: border-box;
            width: 100%;
            min-height: 43px;
            padding: 10px 12px;
            border: 1px solid #dbe3ee;
            border-radius: 10px;
            outline: none;
            background: #fff;
            color: #334155;
            font-family: inherit;
            font-size: 13px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .reports-filter-group input:focus,
        .reports-filter-group select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        .reports-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 43px;
            padding: 10px 15px;
            border: 1px solid #dbe3ee;
            border-radius: 10px;
            background: #fff;
            color: #475569;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
            transition: transform 0.2s, background 0.2s;
        }

        .reports-btn svg {
            width: 16px;
            height: 16px;
        }

        .reports-btn:hover {
            transform: translateY(-1px);
        }

        .reports-btn-primary {
            border-color: #3478f6;
            background: #3478f6;
            color: #fff;
        }

        .reports-btn-primary:hover {
            background: #2563eb;
            color: #fff;
        }

        /* Results information */
        .reports-results-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .reports-count,
        .reports-generated {
            color: #64748b;
            font-size: 13px;
        }

        .reports-count strong {
            color: #0f172a;
        }

        /* Appointment table */
        .reports-table-wrap {
            width: 100%;
            overflow-x: auto;
            border: 1px solid #e8edf5;
            border-radius: 13px;
        }

        .reports-table {
            width: 100%;
            min-width: 1000px;
            margin: 0;
            border-collapse: collapse;
            background: #fff;
        }

        .reports-table th {
            padding: 15px 14px;
            border-bottom: 1px solid #e5ebf3;
            background: #f8fafc;
            color: #64748b;
            font-size: 11px;
            font-weight: 750;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.035em;
            white-space: nowrap;
        }

        .reports-table td {
            padding: 16px 14px;
            border-bottom: 1px solid #edf1f6;
            color: #334155;
            font-size: 13px;
            vertical-align: middle;
        }

        .reports-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .reports-table tbody tr:hover {
            background: #f8fbff;
        }

        .reports-id {
            color: #2563eb;
            font-weight: 750;
            white-space: nowrap;
        }

        .reports-student-name {
            color: #0f172a;
            font-weight: 650;
        }

        .reports-status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 10px;
            border-radius: 20px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 750;
            white-space: nowrap;
        }

        .reports-status::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .reports-status[data-status="scheduled"] {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .reports-status[data-status="completed"] {
            background: #dcfce7;
            color: #15803d;
        }

        .reports-status[data-status="cancelled"] {
            background: #fee2e2;
            color: #b91c1c;
        }

        .reports-empty {
            padding: 42px 20px !important;
            color: #64748b !important;
            text-align: center;
        }

        .reports-empty svg {
            display: block;
            width: 32px;
            height: 32px;
            margin: 0 auto 12px;
            color: #94a3b8;
        }

        /* Responsive layout */
        @media (max-width: 1100px) {
            .reports-page {
                padding: 24px;
            }

            .reports-hero {
                padding: 25px;
            }
        }

        @media (max-width: 700px) {
            .reports-page {
                padding: 18px 14px 28px;
            }

            .reports-hero {
                align-items: flex-start;
                flex-wrap: wrap;
                gap: 13px;
                padding: 22px;
                border-radius: 18px;
            }

            .reports-hero-text h1 {
                font-size: 26px;
            }

            .reports-hero-text p {
                font-size: 13px;
            }

            .reports-print-btn {
                margin-left: 0;
            }

            .reports-card-header,
            .reports-card-body {
                padding: 16px;
            }

            .reports-filter {
                padding: 14px;
            }

            .reports-filter-group {
                flex-basis: 100%;
            }

            .reports-btn {
                flex: 1 1 auto;
            }
        }

        /* Print layout */
        @media print {
            body {
                background: #fff !important;
            }

            .sidebar,
            .no-print,
            .reports-print-btn {
                display: none !important;
            }

            .main-content {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .reports-page {
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .reports-hero {
                min-height: auto;
                padding: 12px 0;
                margin-bottom: 18px;
                border-radius: 0;
                background: #fff !important;
                box-shadow: none !important;
                color: #000 !important;
            }

            .reports-hero::after,
            .reports-hero-icon {
                display: none;
            }

            .reports-hero-text h1,
            .reports-hero-text p {
                color: #000 !important;
            }

            .reports-stats {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 10px !important;
            }

            .reports-stat-card,
            .reports-card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
                break-inside: avoid;
            }

            .reports-stat-card {
                min-height: auto;
                padding: 12px;
            }

            .reports-stat-number {
                font-size: 20px;
            }

            .reports-table-wrap {
                overflow: visible !important;
                border-radius: 0;
            }

            .reports-table {
                width: 100% !important;
                min-width: 0 !important;
                table-layout: auto;
            }

            .reports-table th,
            .reports-table td {
                padding: 7px 5px !important;
                border: 1px solid #ddd !important;
                color: #000 !important;
                font-size: 9px !important;
                overflow-wrap: anywhere;
            }

            .reports-status {
                padding: 0;
                background: transparent !important;
                color: #000 !important;
            }

            .reports-status::before {
                display: none;
            }

            @page {
                size: landscape;
                margin: 12mm;
            }
        }
    </style>
</head>

<body>

<?php require_once __DIR__ . "/includes/sidebar.php"; ?>

<main class="main-content">
    <div class="reports-page">

        <!-- Page header -->
        <header class="reports-hero">
            <div class="reports-hero-icon">
                <i data-lucide="chart-no-axes-combined"></i>
            </div>

            <div class="reports-hero-text">
                <h1>Reports</h1>
                <p>View clinic statistics and appointment records.</p>
            </div>

            <button
                type="button"
                class="reports-print-btn no-print"
                onclick="window.print()"
            >
                <i data-lucide="printer"></i>
                Print Report
            </button>
        </header>

        <!-- Summary statistics -->
        <section class="row g-4 reports-stats">

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="reports-stat-card">
                    <div class="reports-stat-icon blue">
                        <i data-lucide="users"></i>
                    </div>
                    <div class="reports-stat-info">
                        <p class="reports-stat-label">Total Students</p>
                        <h2 class="reports-stat-number">
                            <?= number_format($totalStudents) ?>
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="reports-stat-card">
                    <div class="reports-stat-icon purple">
                        <i data-lucide="heart-pulse"></i>
                    </div>
                    <div class="reports-stat-info">
                        <p class="reports-stat-label">Health Profiles</p>
                        <h2 class="reports-stat-number">
                            <?= number_format($totalHealthProfiles) ?>
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="reports-stat-card">
                    <div class="reports-stat-icon green">
                        <i data-lucide="calendar-check"></i>
                    </div>
                    <div class="reports-stat-info">
                        <p class="reports-stat-label">Total Appointments</p>
                        <h2 class="reports-stat-number">
                            <?= number_format($totalAppointments) ?>
                        </h2>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="reports-stat-card">
                    <div class="reports-stat-icon orange">
                        <i data-lucide="clock"></i>
                    </div>
                    <div class="reports-stat-info">
                        <p class="reports-stat-label">Available Time Slots</p>
                        <h2 class="reports-stat-number">
                            <?= number_format($availableSlots) ?>
                        </h2>
                    </div>
                </div>
            </div>

        </section>

        <!-- Appointment report -->
        <section class="reports-card">

            <div class="reports-card-header">
                <h2>Appointment Report</h2>
                <p>Review and filter clinic appointment records.</p>
            </div>

            <div class="reports-card-body">

                <!-- Filters -->
                <form
                    method="GET"
                    action="reports.php"
                    class="reports-filter no-print"
                >
                    <div class="reports-filter-group">
                        <label for="start_date">Start Date</label>
                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            value="<?= e($startDate) ?>"
                        >
                    </div>

                    <div class="reports-filter-group">
                        <label for="end_date">End Date</label>
                        <input
                            type="date"
                            id="end_date"
                            name="end_date"
                            value="<?= e($endDate) ?>"
                        >
                    </div>

                    <div class="reports-filter-group">
                        <label for="status">Appointment Status</label>
                        <select id="status" name="status">
                            <option value="">All Statuses</option>

                            <?php foreach ($statuses as $status): ?>
                                <option
                                    value="<?= e($status) ?>"
                                    <?= $statusFilter === $status ? "selected" : "" ?>
                                >
                                    <?= e($status) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button
                        type="submit"
                        class="reports-btn reports-btn-primary"
                    >
                        <i data-lucide="filter"></i>
                        Apply Filters
                    </button>

                    <a href="reports.php" class="reports-btn">
                        <i data-lucide="rotate-ccw"></i>
                        Clear
                    </a>
                </form>

                <!-- Results information -->
                <div class="reports-results-info">
                    <span class="reports-count">
                        Records found:
                        <strong><?= number_format(count($appointments)) ?></strong>
                    </span>

                    <span class="reports-generated">
                        Generated: <?= date("F j, Y") ?>
                    </span>
                </div>

                <!-- Appointment table -->
                <div class="reports-table-wrap">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Grade / Section</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Clinic Staff</th>
                                <th>Reason</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (empty($appointments)): ?>
                                <tr>
                                    <td colspan="8" class="reports-empty">
                                        <i data-lucide="calendar-search"></i>
                                        No appointment records found.
                                        <br>
                                        Try changing your date or status filters.
                                    </td>
                                </tr>
                            <?php else: ?>

                                <?php foreach ($appointments as $appointment): ?>
                                    <?php
                                    $statusClass = strtolower(
                                        trim((string) $appointment["status"])
                                    );
                                    ?>

                                    <tr>
                                        <td class="reports-id">
                                            #<?= e($appointment["appointment_id"]) ?>
                                        </td>

                                        <td>
                                            <span class="reports-student-name">
                                                <?= e(
                                                    $appointment["first_name"] . " " .
                                                    $appointment["last_name"]
                                                ) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?= e($appointment["grade_level"]) ?>
                                            /
                                            <?= e($appointment["section"]) ?>
                                        </td>

                                        <td>
                                            <?= e(
                                                date(
                                                    "M d, Y",
                                                    strtotime($appointment["slot_date"])
                                                )
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= e(
                                                date(
                                                    "g:i A",
                                                    strtotime($appointment["start_time"])
                                                )
                                            ) ?>
                                            –
                                            <?= e(
                                                date(
                                                    "g:i A",
                                                    strtotime($appointment["end_time"])
                                                )
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= e($appointment["staff_name"]) ?>
                                        </td>

                                        <td>
                                            <?= e($appointment["reason"]) ?>
                                        </td>

                                        <td>
                                            <span
                                                class="reports-status"
                                                data-status="<?= e($statusClass) ?>"
                                            >
                                                <?= e($appointment["status"]) ?>
                                            </span>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>

    </div>
</main>

<!-- Lucide icons -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
</script>

</body>
</html>
