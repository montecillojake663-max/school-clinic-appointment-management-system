<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/connection.php";
require_once __DIR__ . "/classes/health_profile.php";

$healthProfileObject = new HealthProfile($conn);
$healthProfiles = $healthProfileObject->getHealthProfiles();

$status = $_GET["status"] ?? "";

/*
 * Find students who do not have a health profile yet.
 * This prevents one student from having duplicate profiles.
 */
$studentStatement = $conn->prepare("
    SELECT
        students.student_id,
        students.first_name,
        students.last_name,
        students.grade_level,
        students.section
    FROM students_table AS students
    LEFT JOIN health_profiles_table AS profiles
        ON students.student_id = profiles.student_id
    WHERE profiles.profile_id IS NULL
    ORDER BY students.last_name, students.first_name
");

$studentStatement->execute();
$studentsWithoutProfiles = $studentStatement->fetchAll();

function e(?string $value): string
{
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}

function showValue(?string $value): string
{
    $value = trim($value ?? "");

    return $value === "" ? "None recorded" : e($value);
}

function initials(string $firstName, string $lastName): string
{
    $firstInitial = mb_substr(trim($firstName), 0, 1);
    $lastInitial = mb_substr(trim($lastName), 0, 1);

    return strtoupper($firstInitial . $lastInitial);
}

$totalProfiles = count($healthProfiles);
$studentsAvailable = count($studentsWithoutProfiles);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Health Profiles | SCAMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="includes/sidebar.css">
    <link rel="stylesheet" href="includes/app.css">
    <link rel="stylesheet" href="includes/health_profiles.css">
</head>

<body>

<?php require_once __DIR__ . "/includes/sidebar.php"; ?>

<main class="main-content">
    <div class="container">

        <!-- Page header -->

        <section class="health-hero">

            <div class="health-hero-content">

                <div class="health-hero-icon">
                    <i data-lucide="file-heart"></i>
                </div>

                <div>
                    <h1>Health Profiles</h1>

                    <p>
                        Manage student medical information and emergency details.
                    </p>
                </div>

            </div>

            <button
                type="button"
                class="add-health-button"
                data-bs-toggle="modal"
                data-bs-target="#addHealthProfileModal"
                <?= empty($studentsWithoutProfiles) ? "disabled" : ""; ?>
            >
                <i data-lucide="plus"></i>
                Add Health Profile
            </button>

        </section>

        <!-- Messages -->

        <?php if ($status === "added"): ?>

            <div class="alert alert-success">
                Health profile added successfully.
            </div>

        <?php elseif ($status === "updated"): ?>

            <div class="alert alert-success">
                Health profile updated successfully.
            </div>

        <?php elseif ($status === "deleted"): ?>

            <div class="alert alert-success">
                Health profile deleted successfully.
            </div>

        <?php elseif ($status === "duplicate"): ?>

            <div class="alert alert-warning">
                That student already has a health profile.
            </div>

        <?php elseif ($status === "invalid"): ?>

            <div class="alert alert-danger">
                Please select a valid student.
            </div>

        <?php elseif ($status === "error"): ?>

            <div class="alert alert-danger">
                A database error occurred.
            </div>

        <?php endif; ?>

        <!-- Summary cards -->

        <section class="health-summary">

            <article class="health-summary-card">

                <div class="health-summary-icon health-icon-blue">
                    <i data-lucide="heart-pulse"></i>
                </div>

                <div class="health-summary-details">
                    <span>Total Health Profiles</span>
                    <strong><?= $totalProfiles; ?></strong>
                </div>

            </article>

            <article class="health-summary-card">

                <div class="health-summary-icon health-icon-green">
                    <i data-lucide="user-plus"></i>
                </div>

                <div class="health-summary-details">
                    <span>Students Without Profiles</span>
                    <strong><?= $studentsAvailable; ?></strong>
                </div>

            </article>

        </section>

        <?php if (empty($studentsWithoutProfiles)): ?>

            <div class="alert alert-info">
                Every registered student already has a health profile.
            </div>

        <?php endif; ?>

        <!-- Health Profiles table -->

        <section class="health-card">

            <header class="health-card-header">

                <div>
                    <h2>Student Health Records</h2>

                    <p>
                        Medical information saved for registered students.
                    </p>
                </div>

            </header>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Medical History</th>
                            <th>Allergies</th>
                            <th>Emergency Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($healthProfiles)): ?>

                        <tr>
                            <td colspan="5" class="health-empty">
                                No health profiles found.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($healthProfiles as $profile): ?>

                            <tr>

                                <td>

                                    <div class="health-student">

                                        <div class="health-avatar">
                                            <?= e(initials(
                                                $profile["first_name"],
                                                $profile["last_name"]
                                            )); ?>
                                        </div>

                                        <div class="health-student-details">

                                            <strong>
                                                <?= e(
                                                    $profile["first_name"]
                                                    . " "
                                                    . $profile["last_name"]
                                                ); ?>
                                            </strong>

                                            <span>
                                                <?= e(
                                                    $profile["grade_level"]
                                                    . " - "
                                                    . $profile["section"]
                                                ); ?>
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <td>
                                    <span class="medical-text">
                                        <?= showValue(
                                            $profile["medical_history"]
                                        ); ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="medical-text">
                                        <?= showValue(
                                            $profile["allergies"]
                                        ); ?>
                                    </span>
                                </td>

                                <td>

                                    <span class="emergency-contact">

                                        <i data-lucide="phone"></i>

                                        <?= showValue(
                                            $profile["emergency_contact"]
                                        ); ?>

                                    </span>

                                </td>

                                <td>

                                    <div class="health-actions">

                                        <button
                                            type="button"
                                            class="health-action-button health-edit"
                                            title="Update health profile"
                                            data-bs-toggle="modal"
                                            data-bs-target="#updateProfile<?= (int) $profile["profile_id"]; ?>"
                                        >
                                            <i data-lucide="pencil"></i>
                                        </button>

                                        <button
                                            type="button"
                                            class="health-action-button health-delete"
                                            title="Delete health profile"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteProfile<?= (int) $profile["profile_id"]; ?>"
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

<!-- Add Health Profile Modal -->

<div
    class="modal fade"
    id="addHealthProfileModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form
                action="backend/health_profile_code.php"
                method="POST"
            >

                <div class="modal-header">

                    <h2 class="modal-title fs-5">
                        Add Health Profile
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

                            <?php foreach ($studentsWithoutProfiles as $student): ?>

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
                            Medical History
                        </label>

                        <textarea
                            name="medical_history"
                            class="form-control"
                            rows="3"
                            placeholder="Example: Asthma"
                        ></textarea>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Allergies
                        </label>

                        <textarea
                            name="allergies"
                            class="form-control"
                            rows="2"
                            placeholder="Example: Peanuts"
                        ></textarea>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Emergency Contact
                        </label>

                        <input
                            type="text"
                            name="emergency_contact"
                            class="form-control"
                            placeholder="Example: 09123456789"
                        >

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
                        name="add_health_profile"
                        class="btn btn-primary"
                    >
                        Save Health Profile
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>

<!-- Update and Delete Modals -->

<?php foreach ($healthProfiles as $profile): ?>

    <!-- Update Modal -->

    <div
        class="modal fade"
        id="updateProfile<?= (int) $profile["profile_id"]; ?>"
        tabindex="-1"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <form
                    action="backend/health_profile_code.php"
                    method="POST"
                >

                    <div class="modal-header">

                        <h2 class="modal-title fs-5">
                            Update Health Profile
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
                            name="profile_id"
                            value="<?= (int) $profile["profile_id"]; ?>"
                        >

                        <p class="fw-bold">
                            <?= e(
                                $profile["first_name"]
                                . " "
                                . $profile["last_name"]
                            ); ?>
                        </p>

                        <div class="mb-3">

                            <label class="form-label">
                                Medical History
                            </label>

                            <textarea
                                name="medical_history"
                                class="form-control"
                                rows="3"
                            ><?= e($profile["medical_history"]); ?></textarea>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Allergies
                            </label>

                            <textarea
                                name="allergies"
                                class="form-control"
                                rows="2"
                            ><?= e($profile["allergies"]); ?></textarea>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Emergency Contact
                            </label>

                            <input
                                type="text"
                                name="emergency_contact"
                                class="form-control"
                                value="<?= e(
                                    $profile["emergency_contact"]
                                ); ?>"
                            >

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
                            name="update_health_profile"
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
        id="deleteProfile<?= (int) $profile["profile_id"]; ?>"
        tabindex="-1"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <form
                    action="backend/health_profile_code.php"
                    method="POST"
                >

                    <div class="modal-header">

                        <h2 class="modal-title fs-5">
                            Delete Health Profile
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
                            name="profile_id"
                            value="<?= (int) $profile["profile_id"]; ?>"
                        >

                        <p>
                            Are you sure you want to delete the health profile
                            for

                            <strong>
                                <?= e(
                                    $profile["first_name"]
                                    . " "
                                    . $profile["last_name"]
                                ); ?>
                            </strong>?
                        </p>

                        <p class="text-secondary mb-0">
                            This will remove the student's saved medical
                            information.
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
                            name="delete_health_profile"
                            class="btn btn-danger"
                        >
                            Delete Profile
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