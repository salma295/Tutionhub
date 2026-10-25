
<?php
session_start();
require_once "db.php";

// Only tutors can manage their subjects
if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "tutor"
) {
    header("Location: login.php");
    exit;
}

$tutor_id = (int) $_SESSION["user_id"];

// Create CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$error = "";

// Load available subjects
$stmt = $conn->query(
    "SELECT subject_id, subject_name
     FROM subjects
     ORDER BY subject_name"
);
$allSubjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Save selected subjects
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_token"], $token)
    ) {
        $error = "Invalid request. Please refresh the page and try again.";
    } else {
        $selected = $_POST["subjects"] ?? [];

        if (!is_array($selected)) {
            $selected = [];
        }

        $allowedIds = array_map(
            "intval",
            array_column($allSubjects, "subject_id")
        );

        $selectedIds = [];

        foreach ($selected as $id) {
            $validatedId = filter_var($id, FILTER_VALIDATE_INT);

            if (
                $validatedId === false ||
                !in_array((int)$validatedId, $allowedIds, true)
            ) {
                $error = "Invalid subject selection.";
                break;
            }

            $selectedIds[] = (int)$validatedId;
        }

        $selectedIds = array_values(array_unique($selectedIds));

        if (!$error) {
            try {
                $conn->beginTransaction();

                // Remove previous subject selections
                $stmt = $conn->prepare(
                    "DELETE FROM tutor_subjects WHERE tutor_id = ?"
                );
                $stmt->execute([$tutor_id]);

                // Save the new selections
                $stmt = $conn->prepare(
                    "INSERT INTO tutor_subjects
                     (tutor_id, subject_id)
                     VALUES (?, ?)"
                );

                foreach ($selectedIds as $subjectId) {
                    $stmt->execute([$tutor_id, $subjectId]);
                }

                $conn->commit();

                $message = "Your subjects have been saved successfully!";
                $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
            } catch (Throwable $e) {
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }

                error_log($e->getMessage());
                $error = "Could not save subjects. Please check your database tables.";
            }
        }
    }
}

// Load current selections
$stmt = $conn->prepare(
    "SELECT subject_id
     FROM tutor_subjects
     WHERE tutor_id = ?"
);
$stmt->execute([$tutor_id]);

$currentSubjects = array_map(
    "intval",
    $stmt->fetchAll(PDO::FETCH_COLUMN)
);

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Subjects | TuitionHub</title>

    <link rel="stylesheet" href="register.css">

    <style>
        .subjects-card {
            max-width: 650px;
            margin: 35px auto;
            padding: 30px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 28px rgba(0,0,0,.08);
        }

        .subjects-card h1 {
            color: #087f8c;
            margin-top: 0;
        }

        .subject-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px;
            margin: 10px 0;
            background: #f4fafa;
            border: 1px solid #dceeee;
            border-radius: 9px;
            cursor: pointer;
        }

        .subject-option input {
            width: 18px;
            height: 18px;
            accent-color: #087f8c;
        }

        .save-button {
            width: 100%;
            margin-top: 18px;
            padding: 13px;
            background: #087f8c;
            color: white;
            border: 0;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        .notice {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            background: #e5f7eb;
            color: #176534;
        }

        .error {
            background: #ffeded;
            color: #a11;
        }

        .page-links {
            margin-top: 20px;
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
        }

        .page-links a {
            color: #087f8c;
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
    <section class="subjects-card">
        <h1>Manage Your Subjects</h1>

        <p>Select all the subjects you are qualified to teach.</p>

        <?php if ($message): ?>
            <div class="notice"><?= e($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="notice error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if (empty($allSubjects)): ?>
            <p>No subjects are available. Add subjects to your database first.</p>
        <?php else: ?>

            <form method="POST" action="manage_subjects.php">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION["csrf_token"]) ?>"
                >

                <?php foreach ($allSubjects as $subject): ?>
                    <label class="subject-option">
                        <input
                            type="checkbox"
                            name="subjects[]"
                            value="<?= (int)$subject["subject_id"] ?>"
                            <?= in_array(
                                (int)$subject["subject_id"],
                                $currentSubjects,
                                true
                            ) ? "checked" : "" ?>
                        >

                        <span><?= e($subject["subject_name"]) ?></span>
                    </label>
                <?php endforeach; ?>

                <button class="save-button" type="submit">
                    Save Subjects
                </button>
            </form>

        <?php endif; ?>

        <div class="page-links">
            <a href="dashboard.php">← Dashboard</a>
            <a href="tutors.php">View Tutor Directory →</a>
        </div>
    </section>
</main>

</body>
</html>
