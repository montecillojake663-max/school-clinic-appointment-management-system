<?php

session_start();

require_once __DIR__ . "/../connection.php";
require_once __DIR__ . "/../classes/health_profile.php";

// Make sure the user is logged in.
if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

// Only accept submitted forms.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../health_profiles.php");
    exit;
}

$healthProfileObject = new HealthProfile($conn);

// ADD HEALTH PROFILE
if (isset($_POST["add_health_profile"])) {
    $studentId = filter_input(
        INPUT_POST,
        "student_id",
        FILTER_VALIDATE_INT
    );

    $medicalHistory = trim(
        $_POST["medical_history"] ?? ""
    );

    $allergies = trim(
        $_POST["allergies"] ?? ""
    );

    $emergencyContact = trim(
        $_POST["emergency_contact"] ?? ""
    );

    if (!$studentId) {
        header("Location: ../health_profiles.php?status=invalid");
        exit;
    }

    try {
        $healthProfileObject->addHealthProfile(
            $studentId,
            $medicalHistory,
            $allergies,
            $emergencyContact
        );

        header("Location: ../health_profiles.php?status=added");
    } catch (Throwable $error) {
        header("Location: ../health_profiles.php?status=duplicate");
    }

    exit;
}

// UPDATE HEALTH PROFILE
if (isset($_POST["update_health_profile"])) {
    $profileId = filter_input(
        INPUT_POST,
        "profile_id",
        FILTER_VALIDATE_INT
    );

    $medicalHistory = trim(
        $_POST["medical_history"] ?? ""
    );

    $allergies = trim(
        $_POST["allergies"] ?? ""
    );

    $emergencyContact = trim(
        $_POST["emergency_contact"] ?? ""
    );

    if (!$profileId) {
        header("Location: ../health_profiles.php?status=invalid");
        exit;
    }

    try {
        $healthProfileObject->updateHealthProfile(
            $profileId,
            $medicalHistory,
            $allergies,
            $emergencyContact
        );

        header("Location: ../health_profiles.php?status=updated");
    } catch (Throwable $error) {
        header("Location: ../health_profiles.php?status=error");
    }

    exit;
}

// DELETE HEALTH PROFILE
if (isset($_POST["delete_health_profile"])) {
    $profileId = filter_input(
        INPUT_POST,
        "profile_id",
        FILTER_VALIDATE_INT
    );

    if (!$profileId) {
        header("Location: ../health_profiles.php?status=invalid");
        exit;
    }

    try {
        $healthProfileObject->deleteHealthProfile($profileId);

        header("Location: ../health_profiles.php?status=deleted");
    } catch (Throwable $error) {
        header("Location: ../health_profiles.php?status=error");
    }

    exit;
}

header("Location: ../health_profiles.php");
exit;