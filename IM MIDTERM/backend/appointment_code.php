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

// ADD APPOINTMENT
if (isset($_POST["add_appointment"])) {
    $studentId = filter_input(
        INPUT_POST,
        "student_id",
        FILTER_VALIDATE_INT
    );

    $slotId = filter_input(
        INPUT_POST,
        "slot_id",
        FILTER_VALIDATE_INT
    );

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
        // Check if the time slot is already being used.
        $checkStatement = $conn->prepare("
            SELECT COUNT(*)
            FROM appointments_table
            WHERE slot_id = ?
            AND status != 'Cancelled'
        ");

        $checkStatement->execute([$slotId]);

        if ((int) $checkStatement->fetchColumn() > 0) {
            header("Location: ../appointments.php?status=conflict");
            exit;
        }

        $appointmentObject->addAppointment(
            $studentId,
            $userId,
            $slotId,
            $reason,
            $status
        );

        header("Location: ../appointments.php?status=added");
        exit;
    } catch (Throwable $error) {
        header("Location: ../appointments.php?status=error");
        exit;
    }
}

// UPDATE APPOINTMENT
if (isset($_POST["update_appointment"])) {
    $appointmentId = filter_input(
        INPUT_POST,
        "appointment_id",
        FILTER_VALIDATE_INT
    );

    $studentId = filter_input(
        INPUT_POST,
        "student_id",
        FILTER_VALIDATE_INT
    );

    $slotId = filter_input(
        INPUT_POST,
        "slot_id",
        FILTER_VALIDATE_INT
    );

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
        // Check for another appointment using the selected slot.
        $checkStatement = $conn->prepare("
            SELECT COUNT(*)
            FROM appointments_table
            WHERE slot_id = ?
            AND appointment_id != ?
            AND status != 'Cancelled'
        ");

        $checkStatement->execute([
            $slotId,
            $appointmentId
        ]);

        if ((int) $checkStatement->fetchColumn() > 0) {
            header("Location: ../appointments.php?status=conflict");
            exit;
        }

        $appointmentObject->updateAppointment(
            $appointmentId,
            $studentId,
            $userId,
            $slotId,
            $reason,
            $status
        );

        header("Location: ../appointments.php?status=updated");
        exit;
    } catch (Throwable $error) {
        header("Location: ../appointments.php?status=error");
        exit;
    }
}

// DELETE APPOINTMENT
if (isset($_POST["delete_appointment"])) {
    $appointmentId = filter_input(
        INPUT_POST,
        "appointment_id",
        FILTER_VALIDATE_INT
    );

    if (!$appointmentId) {
        header("Location: ../appointments.php?status=invalid");
        exit;
    }

    try {
        $appointmentObject->deleteAppointment($appointmentId);

        header("Location: ../appointments.php?status=deleted");
        exit;
    } catch (Throwable $error) {
        header("Location: ../appointments.php?status=error");
        exit;
    }
}

header("Location: ../appointments.php");
exit;