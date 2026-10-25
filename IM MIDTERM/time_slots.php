<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/connection.php";
require_once __DIR__ . "/classes/time_slot.php";

$timeSlotObject = new TimeSlot($conn);
$timeSlots = $timeSlotObject->getTimeSlots();

$status = $_GET["status"] ?? "";

function e(?string $value): string
{
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}

/* Count the schedules */

$totalTimeSlots = count($timeSlots);
$availableTimeSlots = 0;
$unavailableTimeSlots = 0;

foreach ($timeSlots as $slot) {
    if ((int) $slot["is_available"] === 1) {
        $availableTimeSlots++;
    } else {
        $unavailableTimeSlots++;
    }
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

    <title>Time Slots | SCAMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="includes/sidebar.css">
    <link rel="stylesheet" href="includes/app.css">
    <link rel="stylesheet" href="includes/time_slots.css">
</head>

<body>

<?php require_once __DIR__ . "/includes/sidebar.php"; ?>

<main class="main-content">
    <div class="container">

        <!-- Header -->

        <section class="time-slots-hero">

            <div class="time-slots-hero-content">

                <div class="time-slots-hero-icon">
                    <i data-lucide="calendar-clock"></i>
                </div>

                <div>
                    <h1>Time Slots</h1>

                    <p>
                        Manage available school clinic schedules.
                    </p>
                </div>

            </div>

            <button
                type="button"
                class="add-time-slot-button"
                data-bs-toggle="modal"
                data-bs-target="#addTimeSlotModal"
            >
                <i data-lucide="plus"></i>
                Add Time Slot
            </button>

        </section>

        <!-- Messages -->

        <?php if ($status === "added"): ?>

            <div class="alert alert-success">
                Time slot added successfully.
            </div>

        <?php elseif ($status === "updated"): ?>

            <div class="alert alert-success">
                Time slot updated successfully.
            </div>

        <?php elseif ($status === "deleted"): ?>

            <div class="alert alert-success">
                Time slot deleted successfully.
            </div>

        <?php elseif ($status === "missing"): ?>

            <div class="alert alert-danger">
                Please complete all fields.
            </div>

        <?php elseif ($status === "invalid_time"): ?>

            <div class="alert alert-danger">
                End time must be later than the start time.
            </div>

        <?php elseif ($status === "duplicate"): ?>

            <div class="alert alert-warning">
                That schedule already exists.
            </div>

        <?php elseif ($status === "in_use"): ?>

            <div class="alert alert-warning">
                This schedule cannot be deleted because an appointment is
                using it.
            </div>

        <?php elseif ($status === "error"): ?>

            <div class="alert alert-danger">
                A database error occurred. Please try again.
            </div>

        <?php endif; ?>

        <!-- Summary cards -->

        <section class="time-slot-summary">

            <article class="time-slot-summary-card">

                <div class="time-slot-summary-icon time-slot-icon-blue">
                    <i data-lucide="calendar-days"></i>
                </div>

                <div class="time-slot-summary-details">
                    <span>Total Time Slots</span>
                    <strong><?= $totalTimeSlots; ?></strong>
                </div>

            </article>

            <article class="time-slot-summary-card">

                <div class="time-slot-summary-icon time-slot-icon-green">
                    <i data-lucide="circle-check-big"></i>
                </div>

                <div class="time-slot-summary-details">
                    <span>Available</span>
                    <strong><?= $availableTimeSlots; ?></strong>
                </div>

            </article>

            <article class="time-slot-summary-card">

                <div class="time-slot-summary-icon time-slot-icon-red">
                    <i data-lucide="circle-x"></i>
                </div>

                <div class="time-slot-summary-details">
                    <span>Unavailable</span>
                    <strong><?= $unavailableTimeSlots; ?></strong>
                </div>

            </article>

        </section>

        <!-- Time Slots table -->

        <section class="time-slots-card">

            <header class="time-slots-card-header">

                <h2>Clinic Schedule</h2>

                <p>
                    Available dates and times for clinic appointments.
                </p>

            </header>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Schedule Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Availability</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($timeSlots)): ?>

                        <tr>
                            <td colspan="5" class="time-slot-empty">
                                No time slots found.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($timeSlots as $slot): ?>

                            <tr>

                                <td>

                                    <div class="slot-date">

                                        <div class="slot-date-icon">
                                            <i data-lucide="calendar"></i>
                                        </div>

                                        <div class="slot-date-details">

                                            <strong>
                                                <?= date(
                                                    "F d, Y",
                                                    strtotime(
                                                        $slot["slot_date"]
                                                    )
                                                ); ?>
                                            </strong>

                                            <span>
                                                Slot #<?= (int) $slot["slot_id"]; ?>
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td>

                                    <span class="slot-time">

                                        <i data-lucide="clock-3"></i>

                                        <?= date(
                                            "g:i A",
                                            strtotime($slot["start_time"])
                                        ); ?>

                                    </span>

                                </td>

                                <td>

                                    <span class="slot-time">

                                        <i data-lucide="clock-3"></i>

                                        <?= date(
                                            "g:i A",
                                            strtotime($slot["end_time"])
                                        ); ?>

                                    </span>

                                </td>

                                <td>

                                    <?php if (
                                        (int) $slot["is_available"] === 1
                                    ): ?>

                                        <span class="availability available">
                                            Available
                                        </span>

                                    <?php else: ?>

                                        <span class="availability unavailable">
                                            Unavailable
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <div class="time-slot-actions">

                                        <button
                                            type="button"
                                            class="time-slot-action-button time-slot-edit"
                                            title="Update time slot"
                                            data-bs-toggle="modal"
                                            data-bs-target="#updateSlot<?= (int) $slot["slot_id"]; ?>"
                                        >
                                            <i data-lucide="pencil"></i>
                                        </button>

                                        <button
                                            type="button"
                                            class="time-slot-action-button time-slot-delete"
                                            title="Delete time slot"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteSlot<?= (int) $slot["slot_id"]; ?>"
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

<!-- Add Time Slot Modal -->

<div
    class="modal fade"
    id="addTimeSlotModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form
                action="backend/time_slot_code.php"
                method="POST"
            >

                <div class="modal-header">

                    <h2 class="modal-title fs-5">
                        Add Time Slot
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
                            Date
                        </label>

                        <input
                            type="date"
                            name="slot_date"
                            class="form-control"
                            min="<?= date("Y-m-d"); ?>"
                            required
                        >

                    </div>

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label">
                                Start Time
                            </label>

                            <input
                                type="time"
                                name="start_time"
                                class="form-control"
                                required
                            >

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                End Time
                            </label>

                            <input
                                type="time"
                                name="end_time"
                                class="form-control"
                                required
                            >

                        </div>

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
                        name="add_time_slot"
                        class="btn btn-primary"
                    >
                        Save Time Slot
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>

<!-- Update and Delete Modals -->

<?php foreach ($timeSlots as $slot): ?>

    <!-- Update Modal -->

    <div
        class="modal fade"
        id="updateSlot<?= (int) $slot["slot_id"]; ?>"
        tabindex="-1"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <form
                    action="backend/time_slot_code.php"
                    method="POST"
                >

                    <div class="modal-header">

                        <h2 class="modal-title fs-5">
                            Update Time Slot
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
                            name="slot_id"
                            value="<?= (int) $slot["slot_id"]; ?>"
                        >

                        <div class="mb-3">

                            <label class="form-label">
                                Date
                            </label>

                            <input
                                type="date"
                                name="slot_date"
                                class="form-control"
                                value="<?= e($slot["slot_date"]); ?>"
                                required
                            >

                        </div>

                        <div class="row g-3">

                            <div class="col-md-6">

                                <label class="form-label">
                                    Start Time
                                </label>

                                <input
                                    type="time"
                                    name="start_time"
                                    class="form-control"
                                    value="<?= e($slot["start_time"]); ?>"
                                    required
                                >

                            </div>

                            <div class="col-md-6">

                                <label class="form-label">
                                    End Time
                                </label>

                                <input
                                    type="time"
                                    name="end_time"
                                    class="form-control"
                                    value="<?= e($slot["end_time"]); ?>"
                                    required
                                >

                            </div>

                        </div>

                        <div class="availability-switch mt-4">

                            <div class="form-check form-switch">

                                <input
                                    type="checkbox"
                                    name="is_available"
                                    class="form-check-input"
                                    id="available<?= (int) $slot["slot_id"]; ?>"
                                    <?= (int) $slot["is_available"] === 1
                                        ? "checked"
                                        : ""; ?>
                                >

                                <label
                                    class="form-check-label"
                                    for="available<?= (int) $slot["slot_id"]; ?>"
                                >
                                    Available for appointments
                                </label>

                            </div>

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
                            name="update_time_slot"
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
        id="deleteSlot<?= (int) $slot["slot_id"]; ?>"
        tabindex="-1"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <form
                    action="backend/time_slot_code.php"
                    method="POST"
                >

                    <div class="modal-header">

                        <h2 class="modal-title fs-5">
                            Delete Time Slot
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
                            name="slot_id"
                            value="<?= (int) $slot["slot_id"]; ?>"
                        >

                        <p>
                            Are you sure you want to delete this schedule?
                        </p>

                        <strong>
                            <?= date(
                                "F d, Y",
                                strtotime($slot["slot_date"])
                            ); ?>

                            at

                            <?= date(
                                "g:i A",
                                strtotime($slot["start_time"])
                            ); ?>
                        </strong>

                        <p class="text-secondary mt-3 mb-0">
                            A schedule being used by an appointment cannot
                            be deleted.
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
                            name="delete_time_slot"
                            class="btn btn-danger"
                        >
                            Delete Time Slot
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