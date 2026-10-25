<?php

require_once "../connection.php";
require_once "../classes/student.php";

$student = new Student($conn);

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../students.php");
    exit;
}

// CREATE STUDENT
if (isset($_POST["add_student"])) {
    $firstName = trim($_POST["first_name"] ?? "");
    $lastName = trim($_POST["last_name"] ?? "");
    $gradeLevel = trim($_POST["grade_level"] ?? "");
    $section = trim($_POST["section"] ?? "");
    $contactNumber = trim($_POST["contact_number"] ?? "");

    if (
        $firstName === "" ||
        $lastName === "" ||
        $gradeLevel === "" ||
        $section === ""
    ) {
        header("Location: ../students.php?status=missing");
        exit;
    }

    $student->addStudent(
        $firstName,
        $lastName,
        $gradeLevel,
        $section,
        $contactNumber
    );

    header("Location: ../students.php?status=added");
    exit;
}

// UPDATE STUDENT
if (isset($_POST["update_student"])) {
    $studentId = filter_input(INPUT_POST, "student_id", FILTER_VALIDATE_INT);
    $firstName = trim($_POST["first_name"] ?? "");
    $lastName = trim($_POST["last_name"] ?? "");
    $gradeLevel = trim($_POST["grade_level"] ?? "");
    $section = trim($_POST["section"] ?? "");
    $contactNumber = trim($_POST["contact_number"] ?? "");

    if (
        !$studentId ||
        $firstName === "" ||
        $lastName === "" ||
        $gradeLevel === "" ||
        $section === ""
    ) {
        header("Location: ../students.php?status=invalid");
        exit;
    }

    $student->updateStudent(
        $studentId,
        $firstName,
        $lastName,
        $gradeLevel,
        $section,
        $contactNumber
    );

    header("Location: ../students.php?status=updated");
    exit;
}

// DELETE STUDENT
if (isset($_POST["delete_student"])) {
    $studentId = filter_input(
        INPUT_POST,
        "student_id",
        FILTER_VALIDATE_INT
    );

    if (!$studentId) {
        header("Location: ../students.php?status=invalid");
        exit;
    }

    try {
        $student->deleteStudent($studentId);
        header("Location: ../students.php?status=deleted");
    } catch (PDOException $error) {
        // A student with an existing appointment cannot be deleted.
        header("Location: ../students.php?status=in_use");
    }

    exit;
}

header("Location: ../students.php");
exit;