
<?php
session_start();

if (
    empty($_SESSION["user_id"]) ||
    empty($_SESSION["role"]) ||
    !in_array($_SESSION["role"], ["student", "tutor"], true)
) {
    header("Location: login.php");
    exit;
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | TuitionHub</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="index.php">
        <span class="brand-icon">T</span>
        <span>Tuition<span class="brand-accent">Hub</span></span>
    </a>
    <nav class="header-nav">
        <a href="index.php">Home</a>
        <a href="dashboard.php" aria-current="page">Dashboard</a>
    </nav>
</header>

<main class="page-container">
    <section class="intro">
        <span class="eyebrow">YOUR TUITIONHUB ACCOUNT</span>
        <h1>
            Welcome, <?= e($_SESSION["full_name"] ?? "User") ?>!
        </h1>
        <p>
            You are logged in as a
            <strong><?= e(ucfirst($_SESSION["role"])) ?></strong>.
        </p>
        <div class="benefit-list">
            <?php if ($_SESSION["role"] === "student"): ?>
                <p>✓ Your student account is active.</p>
                <p>✓ Tutor search and tuition posting are next.</p>
            <?php else: ?>
                <p>✓ Your tutor account is active.</p>
                <p>✓ Tutor profile management is coming next.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="form-card">
        <div class="form-heading">
            <h2>Account overview</h2>
            <p>Your current login information.</p>
        </div>

        <p><strong>Name:</strong> <?= e($_SESSION["full_name"] ?? "") ?></p>
        <p><strong>Email:</strong> <?= e($_SESSION["email"] ?? "") ?></p>
        <p><strong>Role:</strong> <?= e(ucfirst($_SESSION["role"])) ?></p>

        <form method="POST" action="logout.php">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION["csrf_token"] ?? "") ?>"
            >
            <button class="submit-button" type="submit">
                Logout
            </button>
        </form>
    </section>
</main>

<footer class="site-footer">
    <p>© <?= date("Y") ?> TuitionHub. Find your tutor. Shape your future.</p>
</footer>
</body>
</html>
