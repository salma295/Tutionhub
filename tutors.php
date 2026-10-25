
<?php
session_start();
require_once "db.php";

if (empty($_SESSION["user_id"]) || empty($_SESSION["role"])) {
    header("Location: login.php");
    exit;
}

$subjectId = filter_input(INPUT_GET, "subject", FILTER_VALIDATE_INT);
$subjectId = $subjectId && $subjectId > 0 ? $subjectId : 0;

$location = trim($_GET["location"] ?? "");
if (!is_string($location)) {
    $location = "";
}
$location = substr($location, 0, 100);

$subjects = $conn->query(
    "SELECT subject_id, subject_name FROM subjects ORDER BY subject_name"
)->fetchAll(PDO::FETCH_ASSOC);

$sql = "
    SELECT
        t.tutor_id,
        t.full_name,
        t.qualification,
        t.experience_years,
        t.location,
        t.bio,
        GROUP_CONCAT(
            DISTINCT s.subject_name
            ORDER BY s.subject_name SEPARATOR ', '
        ) AS subject_names
    FROM tutors t
    LEFT JOIN tutor_subjects ts ON ts.tutor_id = t.tutor_id
    LEFT JOIN subjects s ON s.subject_id = ts.subject_id
    WHERE 1 = 1
";
$params = [];

if ($subjectId > 0) {
    $sql .= " AND EXISTS (
        SELECT 1 FROM tutor_subjects ts2
        WHERE ts2.tutor_id = t.tutor_id
          AND ts2.subject_id = :subject_id
    )";
    $params[":subject_id"] = $subjectId;
}

if ($location !== "") {
    $sql .= " AND t.location LIKE :location";
    $params[":location"] = "%" . $location . "%";
}

$sql .= "
    GROUP BY
        t.tutor_id, t.full_name, t.qualification,
        t.experience_years, t.location, t.bio
    ORDER BY t.full_name
    LIMIT 100
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$tutors = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Tutors | TuitionHub</title>
    <link rel="stylesheet" href="register.css">
    <style>
        .search-form {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 12px;
            align-items: end;
            margin-bottom: 25px;
        }
        .search-form label { margin-top: 0; }
        .tutor-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 18px;
        }
        .tutor-card {
            background: #fff;
            border: 1px solid #dce5e9;
            border-radius: 14px;
            padding: 22px;
        }
        .tutor-card h3 { margin-top: 0; color: #142b43; }
        .tutor-card p { overflow-wrap: anywhere; }
        @media (max-width: 650px) {
            .search-form { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<header class="site-header">
    <a class="brand" href="index.php">
        <span class="brand-icon">T</span>
        <span>Tuition<span class="brand-accent">Hub</span></span>
    </a>
    <nav class="header-nav">
        <a href="dashboard.php">Dashboard</a>
        <?php if ($_SESSION["role"] === "tutor"): ?>
            <a href="manage_subjects.php">My Subjects</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="page-container" style="display:block;max-width:1100px">
    <section class="intro">
        <span class="eyebrow">TUITIONHUB DIRECTORY</span>
        <h1>Find the right tutor.</h1>
        <p>Search tutor profiles saved in the TuitionHub database.</p>
    </section>

    <section class="form-card" style="margin:25px 0">
        <form method="GET" class="search-form">
            <div>
                <label for="subject">Subject</label>
                <select id="subject" name="subject">
                    <option value="0">All subjects</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option
                            value="<?= (int)$subject["subject_id"] ?>"
                            <?= $subjectId === (int)$subject["subject_id"] ? "selected" : "" ?>
                        >
                            <?= e($subject["subject_name"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="location">Location</label>
                <input
                    id="location"
                    name="location"
                    type="text"
                    maxlength="100"
                    value="<?= e($location) ?>"
                    placeholder="e.g. Mirpur, Dhaka"
                >
            </div>
            <button class="submit-button" style="margin-top:0" type="submit">
                Search
            </button>
        </form>

        <p><?= count($tutors) ?> tutor profile(s) found.</p>

        <?php if (!$tutors): ?>
            <p>No tutors match your search yet. Tutors must register and select their teaching subjects to appear here.</p>
        <?php else: ?>
            <div class="tutor-list">
                <?php foreach ($tutors as $tutor): ?>
                    <article class="tutor-card">
                        <h3><?= e($tutor["full_name"]) ?></h3>
                        <p><strong>Subjects:</strong>
                            <?= e($tutor["subject_names"] ?: "Not selected") ?>
                        </p>
                        <p><strong>Qualification:</strong>
                            <?= e($tutor["qualification"] ?: "Not provided") ?>
                        </p>
                        <p><strong>Experience:</strong>
                            <?= (int)$tutor["experience_years"] ?> year(s)
                        </p>
                        <p><strong>Location:</strong>
                            <?= e($tutor["location"] ?: "Not provided") ?>
                        </p>
                        <a href="tutor_profile.php?id=<?= (int)$tutor["tutor_id"] ?>">
                            View profile →
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
