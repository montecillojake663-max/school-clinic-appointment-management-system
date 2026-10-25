```php
<?php

$currentPage = basename($_SERVER["PHP_SELF"]);

function activePage(string $page, string $currentPage): string
{
    return $page === $currentPage ? "active" : "";
}

?>

<aside class="sidebar">

    <div class="sidebar-brand">

        <div class="brand-icon">
            <i data-lucide="heart-pulse"></i>
        </div>

        <div class="brand-text">
            <strong>SCAMS</strong>
            <span>School Clinic</span>
        </div>

    </div>

    <nav class="sidebar-menu">

        <p class="menu-label">MAIN MENU</p>

        <a
            href="dashboard.php"
            class="sidebar-link <?= activePage("dashboard.php", $currentPage); ?>"
        >
            <i data-lucide="layout-dashboard"></i>
            <span>Dashboard</span>
        </a>

        <a
            href="students.php"
            class="sidebar-link <?= activePage("students.php", $currentPage); ?>"
        >
            <i data-lucide="users"></i>
            <span>Students</span>
        </a>

        <a
            href="health_profiles.php"
            class="sidebar-link <?= activePage("health_profiles.php", $currentPage); ?>"
        >
            <i data-lucide="file-heart"></i>
            <span>Health Profiles</span>
        </a>

        <a
            href="appointments.php"
            class="sidebar-link <?= activePage("appointments.php", $currentPage); ?>"
        >
            <i data-lucide="calendar-check"></i>
            <span>Appointments</span>
        </a>

        <a
            href="time_slots.php"
            class="sidebar-link <?= activePage("time_slots.php", $currentPage); ?>"
        >
            <i data-lucide="clock"></i>
            <span>Time Slots</span>
        </a>

        <a
            href="reports.php"
            class="sidebar-link <?= activePage("reports.php", $currentPage); ?>"
        >
            <i data-lucide="chart-no-axes-combined"></i>
            <span>Reports</span>
        </a>

    </nav>

    <div class="sidebar-bottom">

        <div class="system-status">
            <span class="status-dot"></span>
            <span>System Online</span>
        </div>

        <a href="logout.php" class="sidebar-link logout-link">
            <i data-lucide="log-out"></i>
            <span>Logout</span>
        </a>

    </div>

</aside>

<script src="https://unpkg.com/lucide@latest"></script>

<script>
    lucide.createIcons();
</script>
```