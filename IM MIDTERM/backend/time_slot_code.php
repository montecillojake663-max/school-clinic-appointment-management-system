<?php

session_start();

require_once __DIR__ . "/../connection.php";
require_once __DIR__ . "/../classes/time_slot.php";

// Make sure the user is logged in.
if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

// Only accept submitted forms.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../time_slots.php");
    exit;
}

$timeSlotObject = new TimeSlot($conn);

// ADD TIME SLOT
if (isset($_POST["add_time_slot"])) {
    $slotDate = $_POST["slot_date"] ?? "";
    $startTime = $_POST["start_time"] ?? "";
    $endTime = $_POST["end_time"] ?? "";

    if (
        $slotDate === "" ||
        $startTime === "" ||
        $endTime === ""
    ) {
        header("Location: ../time_slots.php?status=missing");
        exit;
    }

    if ($endTime <= $startTime) {
        header("Location: ../time_slots.php?status=invalid_time");
        exit;
    }

    try {
        $timeSlotObject->addTimeSlot(
            $slotDate,
            $startTime,
            $endTime
        );

        header("Location: ../time_slots.php?status=added");
    } catch (Throwable $error) {
        header("Location: ../time_slots.php?status=duplicate");
    }

    exit;
}

// UPDATE TIME SLOT
if (isset($_POST["update_time_slot"])) {
    $slotId = filter_input(
        INPUT_POST,
        "slot_id",
        FILTER_VALIDATE_INT
    );

    $slotDate = $_POST["slot_date"] ?? "";
    $startTime = $_POST["start_time"] ?? "";
    $endTime = $_POST["end_time"] ?? "";

    $isAvailable = isset($_POST["is_available"])
        ? 1
        : 0;

    if (
        !$slotId ||
        $slotDate === "" ||
        $startTime === "" ||
        $endTime === ""
    ) {
        header("Location: ../time_slots.php?status=missing");
        exit;
    }

    if ($endTime <= $startTime) {
        header("Location: ../time_slots.php?status=invalid_time");
        exit;
    }

    try {
        $timeSlotObject->updateTimeSlot(
            $slotId,
            $slotDate,
            $startTime,
            $endTime,
            $isAvailable
        );

        header("Location: ../time_slots.php?status=updated");
    } catch (Throwable $error) {
        header("Location: ../time_slots.php?status=duplicate");
    }

    exit;
}

// DELETE TIME SLOT
if (isset($_POST["delete_time_slot"])) {
    $slotId = filter_input(
        INPUT_POST,
        "slot_id",
        FILTER_VALIDATE_INT
    );

    if (!$slotId) {
        header("Location: ../time_slots.php?status=invalid");
        exit;
    }

    try {
        $timeSlotObject->deleteTimeSlot($slotId);

        header("Location: ../time_slots.php?status=deleted");
    } catch (Throwable $error) {
        header("Location: ../time_slots.php?status=in_use");
    }

    exit;
}

header("Location: ../time_slots.php");
exit;