
<?php
session_start();
require_once "db.php";

// Only logged-in students and tutors can view profiles
if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    !in_array($_SESSION["role"], ["student", "tutor"], true)
) {
    header("Location: login.php");
    exit;
}

// Validate tutor ID from URL
$tutor_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$tutor_id || $tutor_id < 1) {
    http_response_code(400);
    exit("Invalid tutor ID.");
}

// Find the tutor
$sql = "SELECT tutor_id, full_name, email, phone, qualification,
               experience_years, location, bio
        FROM tutors
        WHERE tutor_id = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$tutor_id]);
$tutor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tutor) {
    http_response_code(404);
    exit("Tutor profile not found.");
}

// Get the tutor's subjects
$sql = "SELECT s.subject_name
        FROM tutor_subjects ts
        JOIN subjects s ON s.subject_id = ts.subject_id
        WHERE ts.tutor_id = ?
        ORDER BY s.subject_name";

$stmt = $conn->prepare($sql);
$stmt->execute([$tutor_id]);
$subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tutor["full_name"]) ?> | TuitionHub</title>
    <link rel="stylesheet" href="register.css">

    <style>
        .profile-card {
            max-width: 700px;
            margin: 35px auto;
            padding: 30px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 28px rgba(0,0,0,.08);
        }

        .profile-card h1 {
            color: #087f8c;
            margin-bottom: 10px;
        }

        .profile-card p {
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        .subject-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 12px 0 22px;
        }

        .subject-tag {
            background: #e4f6f5;
            color: #086b73;
            padding: 7px 12px;
            border-radius: 20px;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #087f8c;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <header class="site-header">
        <a class="brand" href="index.php">TuitionHub</a>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="tutors.php">Find Tutors</a>
        </nav>
    </header>

    <main class="page-container">
        <section class="profile-card">
            <h1><?= e($tutor["full_name"]) ?></h1>
            <p>Meet your potential tutor on TuitionHub.</p>

            <h3>Subjects</h3>
            <div class="subject-list">
                <?php if ($subjects): ?>
                    <?php foreach ($subjects as $subject): ?>
                        <span class="subject-tag">
                            <?= e($subject) ?>
                        </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No subjects added yet.</p>
                <?php endif; ?>
            </div>

            <h3>Qualification</h3>
            <p><?= e($tutor["qualification"] ?: "Not provided") ?></p>

            <h3>Teaching Experience</h3>
            <p>
                <?= e($tutor["experience_years"]) ?> year(s)
            </p>

            <h3>Location</h3>
            <p><?= e($tutor["location"] ?: "Not provided") ?></p>

            <h3>About the Tutor</h3>
            <p><?= nl2br(e($tutor["bio"] ?: "No bio added yet.")) ?></p>

            <a class="back-link" href="tutors.php">
                &larr; Back to Tutors
            </a>
        </section>
    </main>
</body>
</html>
