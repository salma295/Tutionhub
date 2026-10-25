
<?php
session_start();
require_once "db.php";

if (!empty($_SESSION["user_id"]) && !empty($_SESSION["role"])) {
    header("Location: dashboard.php");
    exit;
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$error = "";
$email = "";
$role = "student";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    $submittedRole = $_POST["role"] ?? "";
    $submittedEmail = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_token"], $token)
    ) {
        $error = "Your session expired. Refresh the page and try again.";
    } elseif (
        !is_string($submittedRole) ||
        !in_array($submittedRole, ["student", "tutor"], true)
    ) {
        $error = "Please select a valid account type.";
    } elseif (
        !is_string($submittedEmail) ||
        !filter_var(trim($submittedEmail), FILTER_VALIDATE_EMAIL) ||
        strlen(trim($submittedEmail)) > 150
    ) {
        $error = "Enter a valid email and password.";
    } elseif (!is_string($password)) {
        $error = "Enter a valid email and password.";
    } else {
        $role = $submittedRole;
        $email = trim($submittedEmail);

        // The table name comes only from this fixed allowlist.
        $table = $role === "student" ? "students" : "tutors";
        $idColumn = $role === "student" ? "student_id" : "tutor_id";

        $stmt = $conn->prepare(
            "SELECT $idColumn AS user_id, full_name, email, password_hash
             FROM $table
             WHERE email = :email
             LIMIT 1"
        );

        $stmt->execute([":email" => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password_hash"])) {
            session_regenerate_id(true);

            $_SESSION["user_id"] = (int)$user["user_id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = $role;
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

            header("Location: dashboard.php");
            exit;
        }

        $error = "Invalid account type, email, or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | TuitionHub</title>
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
        <a href="register.php">Register</a>
    </nav>
</header>

<main class="page-container">
    <section class="intro">
        <span class="eyebrow">WELCOME BACK</span>
        <h1>Your next opportunity starts here.</h1>
        <p>Log in to continue your TuitionHub journey.</p>
        <div class="benefit-list">
            <p>✓ Access your account</p>
            <p>✓ Connect as a student or tutor</p>
            <p>✓ Keep your account protected</p>
        </div>
    </section>

    <section class="form-card">
        <div class="form-heading">
            <h2>Login to TuitionHub</h2>
            <p>Enter the details you used to register.</p>
        </div>

        <?php if ($error !== ""): ?>
            <div class="notice error" role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION["csrf_token"]) ?>"
            >

            <label for="role">Login as</label>
            <select id="role" name="role" required>
                <option value="student"
                    <?= $role === "student" ? "selected" : "" ?>>
                    Student / Parent
                </option>
                <option value="tutor"
                    <?= $role === "tutor" ? "selected" : "" ?>>
                    Tutor
                </option>
            </select>

            <label for="email">Email address</label>
            <input
                id="email"
                name="email"
                type="email"
                maxlength="150"
                autocomplete="username"
                value="<?= e($email) ?>"
                required
            >

            <label for="password">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
            >

            <button class="submit-button" type="submit">
                Login <span aria-hidden="true">→</span>
            </button>

            <p class="form-footer">
                Don't have an account?
                <a href="register.php">Register here</a>
            </p>
        </form>
    </section>
</main>

<footer class="site-footer">
    <p>© <?= date("Y") ?> TuitionHub. Find your tutor. Shape your future.</p>
</footer>
</body>
</html>
