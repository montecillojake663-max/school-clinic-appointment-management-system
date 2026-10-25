<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/connection.php";
require_once __DIR__ . "/classes/student.php";

$studentObject = new Student($conn);

$keyword = trim($_GET["search"] ?? "");
$status = $_GET["status"] ?? "";

$totalStudents = $studentObject->getStudentCount();

$students = $keyword !== ""
    ? $studentObject->searchStudents($keyword)
    : $studentObject->getAllStudents();

$resultCount = count($students);

function e(?string $value): string
{
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}

function initials(string $firstName, string $lastName): string
{
    return strtoupper(
        substr($firstName, 0, 1) .
        substr($lastName, 0, 1)
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

    <title>Students | SCAMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="includes/sidebar.css">
    <link rel="stylesheet" href="includes/app.css">
    <link rel="stylesheet" href="includes/students.css">
</head>

<body>

    <?php require_once __DIR__ . "/includes/sidebar.php"; ?>

    <main class="main-content">

        <div class="container">

            <!-- Student page heading -->

            <section class="students-hero">

                <div class="students-hero-content">

                    <div class="students-hero-icon">
                        <i data-lucide="graduation-cap"></i>
                    </div>

                    <div>
                        <h1>Students</h1>

                        <p>
                            Manage student information and clinic records.
                        </p>
                    </div>

                </div>

                <button
                    class="add-student-button"
                    data-bs-toggle="modal"
                    data-bs-target="#addStudentModal"
                >
                    <i data-lucide="user-plus"></i>
                    <span>Add Student</span>
                </button>

            </section>

            <!-- Messages -->

            <?php if ($status === "added"): ?>

                <div class="alert alert-success">
                    Student added successfully.
                </div>

            <?php elseif ($status === "updated"): ?>

                <div class="alert alert-success">
                    Student updated successfully.
                </div>

            <?php elseif ($status === "deleted"): ?>

                <div class="alert alert-success">
                    Student deleted successfully.
                </div>

            <?php elseif ($status === "missing"): ?>

                <div class="alert alert-danger">
                    Please complete all required fields.
                </div>

            <?php elseif ($status === "invalid"): ?>

                <div class="alert alert-danger">
                    Invalid student information.
                </div>

            <?php elseif ($status === "in_use"): ?>

                <div class="alert alert-warning">
                    This student cannot be deleted because an appointment
                    is connected to the record.
                </div>

            <?php endif; ?>

            <!-- Student summary -->

            <section class="student-summary">

                <div class="summary-card">

                    <div class="summary-icon blue-summary">
                        <i data-lucide="users"></i>
                    </div>

                    <div>
                        <span>Total Students</span>
                        <strong><?= $totalStudents; ?></strong>
                    </div>

                </div>

                <div class="summary-card">

                    <div class="summary-icon purple-summary">
                        <i data-lucide="list-filter"></i>
                    </div>

                    <div>
                        <span>Showing Results</span>
                        <strong><?= $resultCount; ?></strong>
                    </div>

                </div>

            </section>

            <!-- Search area -->

            <section class="student-toolbar">

                <form
                    action="students.php"
                    method="GET"
                    class="student-search"
                >

                    <div class="search-input">

                        <i data-lucide="search"></i>

                        <input
                            type="search"
                            name="search"
                            value="<?= e($keyword); ?>"
                            placeholder="Search name, grade, section, contact..."
                        >

                    </div>

                    <button type="submit" class="search-button">
                        Search
                    </button>

                    <?php if ($keyword !== ""): ?>

                        <a href="students.php" class="clear-button">
                            Clear
                        </a>

                    <?php endif; ?>

                </form>

            </section>

            <!-- Students table -->

            <section class="students-card">

                <div class="students-card-header">

                    <div>
                        <h2>Student Directory</h2>

                        <p>
                            All students registered in the clinic system.
                        </p>
                    </div>

                    <?php if ($keyword !== ""): ?>

                        <span class="search-label">
                            Search: “<?= e($keyword); ?>”
                        </span>

                    <?php endif; ?>

                </div>

                <div class="table-responsive">

                    <table class="table student-table align-middle">

                        <thead>

                            <tr>
                                <th>Student</th>
                                <th>Grade Level</th>
                                <th>Section</th>
                                <th>Contact Number</th>
                                <th>Actions</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php if (empty($students)): ?>

                                <tr>

                                    <td colspan="5">

                                        <div class="student-empty">

                                            <i data-lucide="user-x"></i>

                                            <h3>No students found</h3>

                                            <p>
                                                Try another search keyword.
                                            </p>

                                        </div>

                                    </td>

                                </tr>

                            <?php else: ?>

                                <?php foreach ($students as $student): ?>

                                    <tr>

                                        <td>

                                            <div class="student-identity">

                                                <div class="student-avatar">
                                                    <?= e(
                                                        initials(
                                                            $student["first_name"],
                                                            $student["last_name"]
                                                        )
                                                    ); ?>
                                                </div>

                                                <div>

                                                    <strong>
                                                        <?= e(
                                                            $student["first_name"] .
                                                            " " .
                                                            $student["last_name"]
                                                        ); ?>
                                                    </strong>

                                                    <span>
                                                        Student ID:
                                                        <?= (int) $student["student_id"]; ?>
                                                    </span>

                                                </div>

                                            </div>

                                        </td>

                                        <td>
                                            <span class="grade-badge">
                                                <?= e($student["grade_level"]); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?= e($student["section"]); ?>
                                        </td>

                                        <td>

                                            <div class="contact-cell">
                                                <i data-lucide="phone"></i>

                                                <span>
                                                    <?= e(
                                                        $student["contact_number"]
                                                        ?: "No contact"
                                                    ); ?>
                                                </span>
                                            </div>

                                        </td>

                                        <td>

                                            <div class="student-actions">

                                                <button
                                                    class="action-button edit-action"
                                                    title="Update student"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#updateStudent<?= (int) $student["student_id"]; ?>"
                                                >
                                                    <i data-lucide="pencil"></i>
                                                </button>

                                                <button
                                                    class="action-button delete-action"
                                                    title="Delete student"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteStudent<?= (int) $student["student_id"]; ?>"
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

    <!-- Add Student Modal -->

    <div
        class="modal fade"
        id="addStudentModal"
        tabindex="-1"
        aria-hidden="true"
    >

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <form
                    action="backend/student_code.php"
                    method="POST"
                >

                    <div class="modal-header">

                        <div>
                            <h2 class="modal-title fs-5">
                                Add Student
                            </h2>

                            <small class="text-secondary">
                                Enter the student's information.
                            </small>
                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                        ></button>

                    </div>

                    <div class="modal-body">

                        <div class="row g-3">

                            <div class="col-md-6">

                                <label class="form-label">
                                    First Name
                                </label>

                                <input
                                    type="text"
                                    name="first_name"
                                    class="form-control"
                                    required
                                >

                            </div>

                            <div class="col-md-6">

                                <label class="form-label">
                                    Last Name
                                </label>

                                <input
                                    type="text"
                                    name="last_name"
                                    class="form-control"
                                    required
                                >

                            </div>

                            <div class="col-md-6">

                                <label class="form-label">
                                    Grade Level
                                </label>

                                <input
                                    type="text"
                                    name="grade_level"
                                    class="form-control"
                                    placeholder="Example: Grade 10"
                                    required
                                >

                            </div>

                            <div class="col-md-6">

                                <label class="form-label">
                                    Section
                                </label>

                                <input
                                    type="text"
                                    name="section"
                                    class="form-control"
                                    placeholder="Example: Rizal"
                                    required
                                >

                            </div>

                            <div class="col-12">

                                <label class="form-label">
                                    Contact Number
                                </label>

                                <input
                                    type="text"
                                    name="contact_number"
                                    class="form-control"
                                    placeholder="Example: 09123456789"
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
                            name="add_student"
                            class="btn btn-primary"
                        >
                            Save Student
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <!-- Update and Delete Modals -->

    <?php foreach ($students as $student): ?>

        <!-- Update Modal -->

        <div
            class="modal fade"
            id="updateStudent<?= (int) $student["student_id"]; ?>"
            tabindex="-1"
            aria-hidden="true"
        >

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content">

                    <form
                        action="backend/student_code.php"
                        method="POST"
                    >

                        <div class="modal-header">

                            <h2 class="modal-title fs-5">
                                Update Student
                            </h2>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                            ></button>

                        </div>

                        <div class="modal-body">

                            <input
                                type="hidden"
                                name="student_id"
                                value="<?= (int) $student["student_id"]; ?>"
                            >

                            <div class="row g-3">

                                <div class="col-md-6">

                                    <label class="form-label">
                                        First Name
                                    </label>

                                    <input
                                        type="text"
                                        name="first_name"
                                        class="form-control"
                                        value="<?= e($student["first_name"]); ?>"
                                        required
                                    >

                                </div>

                                <div class="col-md-6">

                                    <label class="form-label">
                                        Last Name
                                    </label>

                                    <input
                                        type="text"
                                        name="last_name"
                                        class="form-control"
                                        value="<?= e($student["last_name"]); ?>"
                                        required
                                    >

                                </div>

                                <div class="col-md-6">

                                    <label class="form-label">
                                        Grade Level
                                    </label>

                                    <input
                                        type="text"
                                        name="grade_level"
                                        class="form-control"
                                        value="<?= e($student["grade_level"]); ?>"
                                        required
                                    >

                                </div>

                                <div class="col-md-6">

                                    <label class="form-label">
                                        Section
                                    </label>

                                    <input
                                        type="text"
                                        name="section"
                                        class="form-control"
                                        value="<?= e($student["section"]); ?>"
                                        required
                                    >

                                </div>

                                <div class="col-12">

                                    <label class="form-label">
                                        Contact Number
                                    </label>

                                    <input
                                        type="text"
                                        name="contact_number"
                                        class="form-control"
                                        value="<?= e(
                                            $student["contact_number"]
                                        ); ?>"
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
                                name="update_student"
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
            id="deleteStudent<?= (int) $student["student_id"]; ?>"
            tabindex="-1"
            aria-hidden="true"
        >

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content">

                    <form
                        action="backend/student_code.php"
                        method="POST"
                    >

                        <div class="modal-header">

                            <h2 class="modal-title fs-5">
                                Delete Student
                            </h2>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                            ></button>

                        </div>

                        <div class="modal-body">

                            <input
                                type="hidden"
                                name="student_id"
                                value="<?= (int) $student["student_id"]; ?>"
                            >

                            <p>
                                Are you sure you want to delete
                                <strong>
                                    <?= e(
                                        $student["first_name"] .
                                        " " .
                                        $student["last_name"]
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
                                name="delete_student"
                                class="btn btn-danger"
                            >
                                Delete Student
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