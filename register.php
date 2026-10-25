
<?php
session_start();
require_once "db.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

$errors = [];
$success = "";
$role = "student";

$old = [
    "full_name" => "",
    "email" => "",
    "phone" => "",
    "qualification" => "",
    "experience_years" => "0",
    "location" => "",
    "bio" => ""
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $submittedToken = $_POST["csrf_token"] ?? "";

    if (
        !is_string($submittedToken) ||
        !hash_equals($_SESSION["csrf_token"], $submittedToken)
    ) {
        $errors[] = "Invalid form session. Please refresh and try again.";
    }

    $role = $_POST["role"] ?? "student";

    if (!in_array($role, ["student", "tutor"], true)) {
        $errors[] = "Please select a valid account type.";
        $role = "student";
    }

    foreach ($old as $key => $value) {
        if (isset($_POST[$key]) && is_string($_POST[$key])) {
            $old[$key] = trim($_POST[$key]);
        }
    }

    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if (!is_string($password)) {
        $password = "";
    }

    if (!is_string($confirmPassword)) {
        $confirmPassword = "";
    }

    if ($old["full_name"] === "") {
        $errors[] = "Full name is required.";
    } elseif (strlen($old["full_name"]) > 100) {
        $errors[] = "Full name must be 100 characters or fewer.";
    }

    if (!filter_var($old["email"], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    } elseif (strlen($old["email"]) > 150) {
        $errors[] = "Email must be 150 characters or fewer.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must contain at least 8 characters.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (strlen($old["phone"]) > 20) {
        $errors[] = "Phone number must be 20 characters or fewer.";
    }

    if ($role === "tutor") {
        if ($old["qualification"] === "") {
            $errors[] = "Qualification is required for tutors.";
        } elseif (strlen($old["qualification"]) > 150) {
            $errors[] = "Qualification must be 150 characters or fewer.";
        }

        if ($old["location"] === "") {
            $errors[] = "Location is required for tutors.";
        } elseif (strlen($old["location"]) > 100) {
            $errors[] = "Location must be 100 characters or fewer.";
        }

        $experience = filter_var(
            $old["experience_years"],
            FILTER_VALIDATE_INT
        );

        if (
            $experience === false ||
            $experience < 0 ||
            $experience > 60
        ) {
            $errors[] = "Experience must be a whole number from 0 to 60.";
        }

        if (strlen($old["bio"]) > 5000) {
            $errors[] = "Bio must be 5000 characters or fewer.";
        }
    }

    if (empty($errors)) {
        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        try {
            if ($role === "student") {
                $sql = "INSERT INTO students
                    (full_name, email, password_hash, phone)
                    VALUES (:name, :email, :password, :phone)";

                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ":name" => $old["full_name"],
                    ":email" => $old["email"],
                    ":password" => $passwordHash,
                    ":phone" => $old["phone"] ?: null
                ]);
            } else {
                $sql = "INSERT INTO tutors
                    (full_name, email, password_hash, phone,
                     qualification, experience_years, location, bio)
                    VALUES
                    (:name, :email, :password, :phone,
                     :qualification, :experience, :location, :bio)";

                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ":name" => $old["full_name"],
                    ":email" => $old["email"],
                    ":password" => $passwordHash,
                    ":phone" => $old["phone"] ?: null,
                    ":qualification" => $old["qualification"],
                    ":experience" => (int)$old["experience_years"],
                    ":location" => $old["location"],
                    ":bio" => $old["bio"] ?: null
                ]);
            }

            $success = "Registration successful! Your account has been created.";
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

            foreach ($old as $key => $value) {
                $old[$key] = $key === "experience_years" ? "0" : "";
            }

            $role = "student";
        } catch (PDOException $ex) {
            if ($ex->getCode() === "23000") {
                $errors[] = "This email is already registered for this account type.";
            } else {
                error_log($ex->getMessage());
                $errors[] = "Registration could not be completed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | TuitionHub</title>
    <link rel="stylesheet" href="register.css">
    <script src="register.js" defer></script>
</head>
<body>

<header class="site-header">
    <a class="brand" href="index.php">
        <span class="brand-icon">T</span>
        <span>Tuition<span class="brand-accent">Hub</span></span>
    </a>

    <nav class="header-nav">
        <a href="index.php">Home</a>
        <a href="register.php" aria-current="page">Register</a>
    </nav>
</header>

<main class="page-container">
    <section class="intro">
        <span class="eyebrow">JOIN TUITIONHUB</span>
        <h1>Start your learning journey.</h1>
        <p>
            Create your account to connect with the right people
            and discover new opportunities.
        </p>
        <div class="benefit-list">
            <p>✓ Simple account creation</p>
            <p>✓ Separate student and tutor profiles</p>
            <p>✓ Your password is stored securely as a hash</p>
        </div>
    </section>

    <section class="form-card">
        <div class="form-heading">
            <h2>Create an account</h2>
            <p>Enter your details to get started.</p>
        </div>

        <?php if ($success !== ""): ?>
            <div class="notice success" role="status">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="notice error" role="alert">
                <strong>Please correct the following:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION["csrf_token"]) ?>"
            >

            <label for="role">I want to join as</label>
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

            <label for="full_name">Full name *</label>
            <input
                id="full_name"
                name="full_name"
                type="text"
                maxlength="100"
                autocomplete="name"
                value="<?= e($old["full_name"]) ?>"
                placeholder="Enter your full name"
                required
            >

            <label for="email">Email address *</label>
            <input
                id="email"
                name="email"
                type="email"
                maxlength="150"
                autocomplete="email"
                value="<?= e($old["email"]) ?>"
                placeholder="you@example.com"
                required
            >

            <label for="phone">Phone number</label>
            <input
                id="phone"
                name="phone"
                type="tel"
                maxlength="20"
                autocomplete="tel"
                value="<?= e($old["phone"]) ?>"
                placeholder="Your contact number"
            >

            <div id="tutor-fields" <?= $role !== "tutor" ? "hidden" : "" ?>>
                <div class="section-divider">
                    <span>Tutor profile</span>
                </div>

                <label for="qualification">Qualification *</label>
                <input
                    id="qualification"
                    name="qualification"
                    type="text"
                    maxlength="150"
                    value="<?= e($old["qualification"]) ?>"
                    placeholder="e.g. BSc in Mathematics"
                >

                <label for="experience_years">Teaching experience (years) *</label>
                <input
                    id="experience_years"
                    name="experience_years"
                    type="number"
                    min="0"
                    max="60"
                    step="1"
                    value="<?= e($old["experience_years"]) ?>"
                >

                <label for="location">Location *</label>
                <input
                    id="location"
                    name="location"
                    type="text"
                    maxlength="100"
                    value="<?= e($old["location"]) ?>"
                    placeholder="e.g. Mirpur, Dhaka"
                >

                <label for="bio">Short introduction</label>
                <textarea
                    id="bio"
                    name="bio"
                    rows="4"
                    maxlength="5000"
                    placeholder="Tell students about your teaching experience"
                ><?= e($old["bio"]) ?></textarea>
            </div>

            <label for="password">Password *</label>
            <input
                id="password"
                name="password"
                type="password"
                minlength="8"
                autocomplete="new-password"
                placeholder="At least 8 characters"
                required
            >

            <label for="confirm_password">Confirm password *</label>
            <input
                id="confirm_password"
                name="confirm_password"
                type="password"
                minlength="8"
                autocomplete="new-password"
                placeholder="Enter your password again"
                required
            >

            <button class="submit-button" type="submit">
                Create account <span aria-hidden="true">→</span>
            </button>

            <p class="form-footer">
                Already have an account?
                <a href="login.php">Login</a>
            </p>
        </form>
    </section>
</main>

<footer class="site-footer">
    <p>© <?= date("Y") ?> TuitionHub. Find your tutor. Shape your future.</p>
</footer>

</body>
</html>
