```php
<?php

session_start();

require_once __DIR__ . "/../connection.php";
require_once __DIR__ . "/../classes/appointment.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../appointments.php");
    exit;
}

$appointmentObject = new Appointment($conn);
$userId = (int) $_SESSION["user_id"];

$allowedStatuses = [
    "Scheduled",
    "Completed",
    "Cancelled"
];

// ADD APPOINTMENT — TRANSACTION 1
if (isset($_POST["add_appointment"])) {
    $studentId = filter_input(INPUT_POST, "student_id", FILTER_VALIDATE_INT);
    $slotId = filter_input(INPUT_POST, "slot_id", FILTER_VALIDATE_INT);
    $reason = trim($_POST["reason"] ?? "");
    $status = trim($_POST["status"] ?? "Scheduled");

    if (
        !$studentId ||
        !$slotId ||
        $reason === "" ||
        !in_array($status, $allowedStatuses, true)
    ) {
        header("Location: ../appointments.php?status=invalid");
        exit;
    }

    try {
        $conn->beginTransaction();

        // Lock the selected slot so two requests cannot reserve it simultaneously.
        $slotStatement = $conn->prepare("
            SELECT is_available
            FROM time_slots_table
            WHERE slot_id = ?
            FOR UPDATE
        ");
        $slotStatement->execute([$slotId]);
        $slot = $slotStatement->fetch(PDO::FETCH_ASSOC);

        if (!$slot || (int) $slot["is_available"] !== 1) {
            $conn->rollBack();
            header("Location: ../appointments.php?status=conflict");
            exit;
        }

        // Cancelled appointments do not reserve a slot.
        if ($status !== "Cancelled") {
            $reserveStatement = $conn->prepare("
                UPDATE time_slots_table
                SET is_available = 0
                WHERE slot_id = ? AND is_available = 1
            ");
            $reserveStatement->execute([$slotId]);

            if ($reserveStatement->rowCount() !== 1) {
                $conn->rollBack();
                header("Location: ../appointments.php?status=conflict");
                exit;
            }
        }

        $appointmentObject->addAppointment(
            $studentId,
            $userId,
            $slotId,
            $reason,
            $status
        );

        $conn->commit();

        header("Location: ../appointments.php?status=added");
        exit;
    } catch (Throwable $error) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        header("Location: ../appointments.php?status=error");
        exit;
    }
}

// UPDATE APPOINTMENT — TRANSACTION 2
if (isset($_POST["update_appointment"])) {
    $appointmentId = filter_input(INPUT_POST, "appointment_id", FILTER_VALIDATE_INT);
    $studentId = filter_input(INPUT_POST, "student_id", FILTER_VALIDATE_INT);
    $slotId = filter_input(INPUT_POST, "slot_id", FILTER_VALIDATE_INT);
    $reason = trim($_POST["reason"] ?? "");
    $status = trim($_POST["status"] ?? "");

    if (
        !$appointmentId ||
        !$studentId ||
        !$slotId ||
        $reason === "" ||
        !in_array($status, $allowedStatuses, true)
    ) {
        header("Location: ../appointments.php?status=invalid");
        exit;
    }

    try {
        $conn->beginTransaction();

        // Lock and retrieve the existing appointment.
        $appointmentStatement = $conn->prepare("
            SELECT slot_id, status
            FROM appointments_table
            WHERE appointment_id = ?
            FOR UPDATE
        ");
        $appointmentStatement->execute([$appointmentId]);
        $existingAppointment = $appointmentStatement->fetch(PDO::FETCH_ASSOC);

        if (!$existingAppointment) {
            $conn->rollBack();
            header("Location: ../appointments.php?status=error");
            exit;
        }

        $oldSlotId = (int) $existingAppointment["slot_id"];
        $oldStatus = $existingAppointment["status"];

        // Lock the old and new slots in a consistent order.
        $slotIds = array_values(array_unique([$oldSlotId, $slotId]));
        sort($slotIds, SORT_NUMERIC);

        $slotLock = $conn->prepare("
            SELECT slot_id, is_available
            FROM time_slots_table
            WHERE slot_id = ?
            FOR UPDATE
        ");

        $lockedSlots = [];

        foreach ($slotIds as $id) {
            $slotLock->execute([$id]);
            $slotData = $slotLock->fetch(PDO::FETCH_ASSOC);

            if (!$slotData) {
                $conn->rollBack();
                header("Location: ../appointments.php?status=conflict");
                exit;
            }

            $lockedSlots[$id] = (int) $slotData["is_available"];
        }

        $oldSlotWasReserved = $oldStatus !== "Cancelled";
        $newSlotNeedsReservation = $status !== "Cancelled";

        // A different new slot must be available if the new status reserves it.
        if (
            $slotId !== $oldSlotId &&
            $newSlotNeedsReservation &&
            $lockedSlots[$slotId] !== 1
        ) {
            $conn->rollBack();
            header("Location: ../appointments.php?status=conflict");
            exit;
        }

        // Release the old slot if the appointment moves or becomes cancelled.
        if (
            $oldSlotWasReserved &&
            ($slotId !== $oldSlotId || !$newSlotNeedsReservation)
        ) {
            $releaseStatement = $conn->prepare("
                UPDATE time_slots_table
                SET is_available = 1
                WHERE slot_id = ?
            ");
            $releaseStatement->execute([$oldSlotId]);
        }

        // Reserve the new slot if needed.
        if (
            $newSlotNeedsReservation &&
            ($slotId !== $oldSlotId || !$oldSlotWasReserved)
        ) {
            $reserveStatement = $conn->prepare("
                UPDATE time_slots_table
                SET is_available = 0
                WHERE slot_id = ? AND is_available = 1
            ");
            $reserveStatement->execute([$slotId]);

            if ($reserveStatement->rowCount() !== 1) {
                $conn->rollBack();
                header("Location: ../appointments.php?status=conflict");
                exit;
            }
        }

        $appointmentObject->updateAppointment(
            $appointmentId,
            $studentId,
            $userId,
            $slotId,
            $reason,
            $status
        );

        $conn->commit();

        header("Location: ../appointments.php?status=updated");
        exit;
    } catch (Throwable $error) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        header("Location: ../appointments.php?status=error");
        exit;
    }
}

// DELETE APPOINTMENT — TRANSACTION 3
if (isset($_POST["delete_appointment"])) {
    $appointmentId = filter_input(INPUT_POST, "appointment_id", FILTER_VALIDATE_INT);

    if (!$appointmentId) {
        header("Location: ../appointments.php?status=invalid");
        exit;
    }

    try {
        $conn->beginTransaction();

        // Lock and retrieve the appointment before deleting it.
        $appointmentStatement = $conn->prepare("
            SELECT slot_id, status
            FROM appointments_table
            WHERE appointment_id = ?
            FOR UPDATE
        ");
        $appointmentStatement->execute([$appointmentId]);
        $appointment = $appointmentStatement->fetch(PDO::FETCH_ASSOC);

        if (!$appointment) {
            $conn->rollBack();
            header("Location: ../appointments.php?status=error");
            exit;
        }

        $slotId = (int) $appointment["slot_id"];
        $status = $appointment["status"];

        // Lock the related slot.
        $slotStatement = $conn->prepare("
            SELECT slot_id
            FROM time_slots_table
            WHERE slot_id = ?
            FOR UPDATE
        ");
        $slotStatement->execute([$slotId]);

        if (!$slotStatement->fetch(PDO::FETCH_ASSOC)) {
            $conn->rollBack();
            header("Location: ../appointments.php?status=error");
            exit;
        }

        $appointmentObject->deleteAppointment($appointmentId);

        // Only release a slot that this appointment had reserved.
        if ($status !== "Cancelled") {
            $releaseStatement = $conn->prepare("
                UPDATE time_slots_table
                SET is_available = 1
                WHERE slot_id = ?
            ");
            $releaseStatement->execute([$slotId]);
        }

        $conn->commit();

        header("Location: ../appointments.php?status=deleted");
        exit;
    } catch (Throwable $error) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        header("Location: ../appointments.php?status=error");
        exit;
    }
}

header("Location: ../appointments.php");
exit;
```
