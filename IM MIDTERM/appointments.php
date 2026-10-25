<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/connection.php";
require_once __DIR__ . "/classes/appointment.php";

$appointmentObject = new Appointment($conn);

$keyword = trim($_GET["search"] ?? "");
$status = $_GET["status"] ?? "";

$allAppointments = $appointmentObject->getAppointments();

if ($keyword !== "") {
    $appointments = $appointmentObject->searchAppointments($keyword);
} else {
    $appointments = $allAppointments;
}

/* Count appointment statuses */

$totalAppointments = count($allAppointments);
$scheduledAppointments = 0;
$completedAppointments = 0;

foreach ($allAppointments as $item) {
    if ($item["status"] === "Scheduled") {
        $scheduledAppointments++;
    }

    if ($item["status"] === "Completed") {
        $completedAppointments++;
    }
}

/* Get students for the form */

$studentStatement = $conn->prepare("
    SELECT
        student_id,
        first_name,
        last_name,
        grade_level,
        section
    FROM students_table
    ORDER BY first_name, last_name
");

$studentStatement->execute();
$students = $studentStatement->fetchAll();

/* Get present and future time slots */

$slotStatement = $conn->prepare("
    SELECT
        slot_id,
        slot_date,
        start_time,
        end_time
    FROM time_slots_table
    WHERE slot_date >= CURDATE()
    ORDER BY slot_date, start_time
");

$slotStatement->execute();
$timeSlots = $slotStatement->fetchAll();

function e(?string $value): string
{
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}

function formatSlot(array $slot): string
{
    return date("M d, Y", strtotime($slot["slot_date"]))
        . " | "
        . date("g:i A", strtotime($slot["start_time"]))
        . " - "
        . date("g:i A", strtotime($slot["end_time"]));
}

function appointmentInitials(string $firstName, string $lastName): string
{
    return strtoupper(
        substr(trim($firstName), 0, 1)
        . substr(trim($lastName), 0, 1)
    );
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

    <title>Appointments | SCAMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="includes/sidebar.css">
    <link rel="stylesheet" href="includes/app.css">
    <link rel="stylesheet" href="includes/appointments.css">
</head>

<body>

<?php require_once __DIR__ . "/includes/sidebar.php"; ?>

<main class="main-content">
    <div class="container">

        <!-- Header -->

        <section class="appointments-hero">

            <div class="appointments-hero-content">

                <div class="appointments-hero-icon">
                    <i data-lucide="calendar-check"></i>
                </div>

                <div>
                    <h1>Appointments</h1>

                    <p>
                        Schedule and manage student clinic appointments.
                    </p>
                </div>

            </div>

            <button
                type="button"
                class="add-appointment-button"
                data-bs-toggle="modal"
                data-bs-target="#addAppointmentModal"
            >
                <i data-lucide="plus"></i>
                Add Appointment
            </button>

        </section>

        <!-- Messages -->

        <?php if ($status === "added"): ?>

            <div class="alert alert-success">
                Appointment added successfully.
            </div>

        <?php elseif ($status === "updated"): ?>

            <div class="alert alert-success">
                Appointment updated successfully.
            </div>

        <?php elseif ($status === "deleted"): ?>

            <div class="alert alert-success">
                Appointment deleted successfully.
            </div>

        <?php elseif ($status === "conflict"): ?>

            <div class="alert alert-warning">
                That time slot is already being used.
            </div>

        <?php elseif ($status === "invalid"): ?>

            <div class="alert alert-danger">
                Please complete the appointment form correctly.
            </div>

        <?php elseif ($status === "error"): ?>

            <div class="alert alert-danger">
                A database error occurred. Please try again.
            </div>

        <?php endif; ?>

        <!-- Summary cards -->

        <section class="appointment-summary">

            <article class="appointment-summary-card">

                <div class="appointment-summary-icon appointment-icon-blue">
                    <i data-lucide="calendar-days"></i>
                </div>

                <div class="appointment-summary-details">
                    <span>Total Appointments</span>
                    <strong><?= $totalAppointments; ?></strong>
                </div>

            </article>

            <article class="appointment-summary-card">

                <div class="appointment-summary-icon appointment-icon-orange">
                    <i data-lucide="clock-3"></i>
                </div>

                <div class="appointment-summary-details">
                    <span>Scheduled</span>
                    <strong><?= $scheduledAppointments; ?></strong>
                </div>

            </article>

            <article class="appointment-summary-card">

                <div class="appointment-summary-icon appointment-icon-green">
                    <i data-lucide="circle-check-big"></i>
                </div>

                <div class="appointment-summary-details">
                    <span>Completed</span>
                    <strong><?= $completedAppointments; ?></strong>
                </div>

            </article>

        </section>

        <!-- Search -->

        <section class="appointment-toolbar">

            <form
                action="appointments.php"
                method="GET"
                class="appointment-search"
            >

                <div class="appointment-search-input">

                    <i data-lucide="search"></i>

                    <input
                        type="search"
                        name="search"
                        value="<?= e($keyword); ?>"
                        placeholder="Search student, reason, status or date..."
                    >

                </div>

                <button
                    type="submit"
                    class="appointment-search-button"
                >
                    Search
                </button>

                <?php if ($keyword !== ""): ?>

                    <a
                        href="appointments.php"
                        class="appointment-clear-button"
                    >
                        Clear
                    </a>

                <?php endif; ?>

            </form>

        </section>

        <!-- Appointments table -->

        <section class="appointments-card">

            <header class="appointments-card-header">

                <div>
                    <h2>Appointment Directory</h2>

                    <p>
                        Student clinic schedules and appointment information.
                    </p>
                </div>

                <?php if ($keyword !== ""): ?>

                    <span class="appointment-search-label">
                        <?= count($appointments); ?> result(s)
                    </span>

                <?php endif; ?>

            </header>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Schedule</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Clinic Staff</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($appointments)): ?>

                        <tr>
                            <td colspan="6" class="appointment-empty">

                                <?php if ($keyword !== ""): ?>
                                    No appointments matched your search.
                                <?php else: ?>
                                    No appointments found.
                                <?php endif; ?>

                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($appointments as $appointment): ?>

                            <?php
                            $statusClass = strtolower(
                                $appointment["status"]
                            );
                            ?>

                            <tr>

                                <td>

                                    <div class="appointment-student">

                                        <div class="appointment-avatar">
                                            <?= e(appointmentInitials(
                                                $appointment["first_name"],
                                                $appointment["last_name"]
                                            )); ?>
                                        </div>

                                        <div class="appointment-student-details">

                                            <strong>
                                                <?= e(
                                                    $appointment["first_name"]
                                                    . " "
                                                    . $appointment["last_name"]
                                                ); ?>
                                            </strong>

                                            <span>
                                                <?= e(
                                                    $appointment["grade_level"]
                                                    . " - "
                                                    . $appointment["section"]
                                                ); ?>
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td>

                                    <div class="appointment-schedule">

                                        <strong>
                                            <?= date(
                                                "M d, Y",
                                                strtotime(
                                                    $appointment["slot_date"]
                                                )
                                            ); ?>
                                        </strong>

                                        <span>

                                            <i data-lucide="clock"></i>

                                            <?= date(
                                                "g:i A",
                                                strtotime(
                                                    $appointment["start_time"]
                                                )
                                            ); ?>

                                            -

                                            <?= date(
                                                "g:i A",
                                                strtotime(
                                                    $appointment["end_time"]
                                                )
                                            ); ?>

                                        </span>

                                    </div>

                                </td>

                                <td>
                                    <span class="appointment-reason">
                                        <?= e($appointment["reason"]); ?>
                                    </span>
                                </td>

                                <td>

                                    <span
                                        class="status-badge status-<?= e(
                                            $statusClass
                                        ); ?>"
                                    >
                                        <?= e($appointment["status"]); ?>
                                    </span>

                                </td>

                                <td>

                                    <span class="appointment-staff">

                                        <i data-lucide="user-round"></i>

                                        <?= e($appointment["staff_name"]); ?>

                                    </span>

                                </td>

                                <td>

                                    <div class="appointment-actions">

                                        <button
                                            type="button"
                                            class="appointment-action-button appointment-edit"
                                            title="Update appointment"
                                            data-bs-toggle="modal"
                                            data-bs-target="#updateAppointment<?= (int) $appointment["appointment_id"]; ?>"
                                        >
                                            <i data-lucide="pencil"></i>
                                        </button>

                                        <button
                                            type="button"
                                            class="appointment-action-button appointment-delete"
                                            title="Delete appointment"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteAppointment<?= (int) $appointment["appointment_id"]; ?>"
                                        >
                                            <i data-lucide="trash-2"></i>
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </div>
</main>

<!-- Add Appointment Modal -->

<div
    class="modal fade"
    id="addAppointmentModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form
                action="backend/appointment_code.php"
                method="POST"
            >

                <div class="modal-header">

                    <h2 class="modal-title fs-5">
                        Add Appointment
                    </h2>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">
                            Student
                        </label>

                        <select
                            name="student_id"
                            class="form-select"
                            required
                        >
                            <option value="">
                                Select a student
                            </option>

                            <?php foreach ($students as $student): ?>

                                <option
                                    value="<?= (int) $student["student_id"]; ?>"
                                >
                                    <?= e(
                                        $student["first_name"]
                                        . " "
                                        . $student["last_name"]
                                        . " - "
                                        . $student["grade_level"]
                                        . " "
                                        . $student["section"]
                                    ); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Time Slot
                        </label>

                        <select
                            name="slot_id"
                            class="form-select"
                            required
                        >
                            <option value="">
                                Select a schedule
                            </option>

                            <?php foreach ($timeSlots as $slot): ?>

                                <option
                                    value="<?= (int) $slot["slot_id"]; ?>"
                                >
                                    <?= e(formatSlot($slot)); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Reason for Visit
                        </label>

                        <textarea
                            name="reason"
                            class="form-control"
                            rows="3"
                            placeholder="Enter the reason for the clinic visit"
                            required
                        ></textarea>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Status
                        </label>

                        <select
                            name="status"
                            class="form-select"
                            required
                        >
                            <option value="Scheduled">Scheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        name="add_appointment"
                        class="btn btn-primary"
                    >
                        Save Appointment
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>

<!-- Update and Delete Modals -->

<?php foreach ($appointments as $appointment): ?>

    <!-- Update Modal -->

    <div
        class="modal fade"
        id="updateAppointment<?= (int) $appointment["appointment_id"]; ?>"
        tabindex="-1"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <form
                    action="backend/appointment_code.php"
                    method="POST"
                >

                    <div class="modal-header">

                        <h2 class="modal-title fs-5">
                            Update Appointment
                        </h2>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>

                    </div>

                    <div class="modal-body">

                        <input
                            type="hidden"
                            name="appointment_id"
                            value="<?= (int) $appointment["appointment_id"]; ?>"
                        >

                        <div class="mb-3">

                            <label class="form-label">
                                Student
                            </label>

                            <select
                                name="student_id"
                                class="form-select"
                                required
                            >

                                <?php foreach ($students as $student): ?>

                                    <option
                                        value="<?= (int) $student["student_id"]; ?>"
                                        <?=
                                        (int) $student["student_id"]
                                        === (int) $appointment["student_id"]
                                            ? "selected"
                                            : "";
                                        ?>
                                    >
                                        <?= e(
                                            $student["first_name"]
                                            . " "
                                            . $student["last_name"]
                                        ); ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Time Slot
                            </label>

                            <select
                                name="slot_id"
                                class="form-select"
                                required
                            >

                                <?php foreach ($timeSlots as $slot): ?>

                                    <option
                                        value="<?= (int) $slot["slot_id"]; ?>"
                                        <?=
                                        (int) $slot["slot_id"]
                                        === (int) $appointment["slot_id"]
                                            ? "selected"
                                            : "";
                                        ?>
                                    >
                                        <?= e(formatSlot($slot)); ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Reason
                            </label>

                            <textarea
                                name="reason"
                                class="form-control"
                                rows="3"
                                required
                            ><?= e($appointment["reason"]); ?></textarea>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Status
                            </label>

                            <select
                                name="status"
                                class="form-select"
                                required
                            >

                                <?php
                                $appointmentStatuses = [
                                    "Scheduled",
                                    "Completed",
                                    "Cancelled"
                                ];
                                ?>

                                <?php foreach (
                                    $appointmentStatuses as $appointmentStatus
                                ): ?>

                                    <option
                                        value="<?= e($appointmentStatus); ?>"
                                        <?=
                                        $appointmentStatus
                                        === $appointment["status"]
                                            ? "selected"
                                            : "";
                                        ?>
                                    >
                                        <?= e($appointmentStatus); ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            name="update_appointment"
                            class="btn btn-primary"
                        >
                            Save Changes
                        </button>

                    </div>

                </form>

            </div>

        </div>
    </div>

    <!-- Delete Modal -->

    <div
        class="modal fade"
        id="deleteAppointment<?= (int) $appointment["appointment_id"]; ?>"
        tabindex="-1"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <form
                    action="backend/appointment_code.php"
                    method="POST"
                >

                    <div class="modal-header">

                        <h2 class="modal-title fs-5">
                            Delete Appointment
                        </h2>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>

                    </div>

                    <div class="modal-body">

                        <input
                            type="hidden"
                            name="appointment_id"
                            value="<?= (int) $appointment["appointment_id"]; ?>"
                        >

                        <p>
                            Are you sure you want to delete the appointment for

                            <strong>
                                <?= e(
                                    $appointment["first_name"]
                                    . " "
                                    . $appointment["last_name"]
                                ); ?>
                            </strong>?
                        </p>

                        <p class="text-secondary mb-0">
                            This action cannot be undone.
                        </p>

                    </div>

                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            name="delete_appointment"
                            class="btn btn-danger"
                        >
                            Delete Appointment
                        </button>

                    </div>

                </form>

            </div>

        </div>
    </div>

<?php endforeach; ?>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>

<script src="https://unpkg.com/lucide@latest"></script>

<script>
    lucide.createIcons();
</script>

</body>
</html>