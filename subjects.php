
<?php

// SECTION 1: DATABASE CONNECTION

require_once 'config.php';

try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    #die("Database connection failed.");
    die("Database connection failed: " . $e->getMessage());
}

// Variables used for messages and the selected subject
$error = "";
$message = "";
$selectedSubject = isset($_GET['subject'])
    ? trim($_GET['subject'])
    : "";



// SECTION 2: ADD SUBJECT
// Validate the name, prevent duplicates, and insert a subject

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $name = trim($_POST['subjectName'] ?? '');

    if ($action === 'add') {

        // Check for an empty subject name
        if ($name === '') {
            $error = "Please enter a subject name.";
        } elseif (strlen($name) > 255) {
            $error = "Subject name is too long.";
        } else {

            // Integrity check: prevent duplicate subject names
            $check = $pdo->prepare(
                "SELECT COUNT(*) FROM subjects
                 WHERE LOWER(TRIM(SubjectName)) = LOWER(?)"
            );
            $check->execute([$name]);

            if ($check->fetchColumn() > 0) {
                $error = "That subject already exists.";
            } else {

                // Insert the new subject into the database
                $insert = $pdo->prepare(
                    "INSERT INTO subjects (SubjectName) VALUES (?)"
                );
                $insert->execute([$name]);

                $message = "Subject added successfully.";
                $selectedSubject = $name;
            }
        }
    }


    
    // SECTION 3: DELETE SUBJECT
    // Delete all matching records after user confirmation

    if ($action === 'delete') {

        if ($name === '') {
            $error = "No subject was selected.";
        } else {

            // Delete every record with this exact subject name.
            // This also removes existing duplicate records.
            $delete = $pdo->prepare(
                "DELETE FROM subjects
                 WHERE BINARY SubjectName = BINARY ?"
            );
            $delete->execute([$name]);

            if ($delete->rowCount() > 0) {
                $message = "Subject deleted successfully.";
                $selectedSubject = "";
            } else {
                $error = "Subject could not be found.";
            }
        }
    }
}



// SECTION 4: VIEW ALL SUBJECTS
// Retrieve unique subject names in alphabetical order


$stmt = $pdo->query(
    "SELECT DISTINCT SubjectName
     FROM subjects
     ORDER BY SubjectName"
);

$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);



// SECTION 5: SELECT AND VIEW SUBJECT DETAILS
// Check whether the selected subject exists

$subjectExists = false;

foreach ($subjects as $subject) {
    if ($subject['SubjectName'] === $selectedSubject) {
        $subjectExists = true;
        break;
    }
}

if (!$subjectExists) {
    $selectedSubject = "";
}


// SECTION 6: OUTPUT SECURITY
// Escape text before displaying it in HTML

function escapeHTML($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Art Gallery - Subjects</title>

    <link rel="stylesheet" href="semantic/semantic.css">

    <style>
        body {
            padding: 30px;
            background-color: #f8f9fa;
        }

        .page-container {
            max-width: 1100px;
            margin: 0 auto;
        }

        .subject-list {
            max-height: 500px;
            overflow-y: auto;
        }

        .subject-item {
            display: block;
            padding: 12px 15px;
            border-bottom: 1px solid #eeeeee;
            color: #333333;
        }

        .subject-item:hover {
            background-color: #f3f4f5;
        }

        .subject-item.active {
            background-color: #e8f4ff;
            font-weight: bold;
        }

        .subject-item:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body>
<div class="page-container">

    <h1 class="ui header">
        Art Gallery - Subjects
    </h1>

    <!-- =====================================================
         SECTION 7: ADD SUBJECT USER INTERFACE
         Form for entering and adding a new subject
         ===================================================== -->

    <div class="ui segment">
        <h3 class="ui header">Add a Subject</h3>

        <?php if ($error !== ""): ?>
            <div class="ui negative message">
                <?= escapeHTML($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($message !== ""): ?>
            <div class="ui positive message">
                <?= escapeHTML($message) ?>
            </div>
        <?php endif; ?>

        <form class="ui form" method="post">
            <input type="hidden" name="action" value="add">

            <div class="field">
                <label for="subjectName">Subject Name</label>

                <input
                    type="text"
                    id="subjectName"
                    name="subjectName"
                    placeholder="Enter a subject name"
                    maxlength="255"
                    required
                >
            </div>

            <button class="ui primary button" type="submit">
                Add Subject
            </button>
        </form>
    </div>


    <div class="ui stackable grid">

        <!-- =================================================
             SECTION 8: VIEW SUBJECT LIST USER INTERFACE
             Display each unique subject once
             ================================================= -->

        <div class="six wide column">
            <div class="ui segment">
                <h3 class="ui header">
                    Subjects (<?= count($subjects) ?>)
                </h3>

                <div class="subject-list">

                    <?php if (count($subjects) === 0): ?>

                        <p>No subjects found.</p>

                    <?php else: ?>

                        <?php foreach ($subjects as $subject): ?>

                            <?php
                                $subjectName = $subject['SubjectName'];
                                $isActive = (
                                    $selectedSubject !== "" &&
                                    $selectedSubject === $subjectName
                                );
                            ?>

                            <a
                                class="subject-item <?= $isActive ? 'active' : '' ?>"
                                href="subjects.php?subject=<?= urlencode($subjectName) ?>"
                            >
                                <?= escapeHTML($subjectName) ?>
                            </a>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>
            </div>
        </div>


        <!-- =================================================
             SECTION 9: SUBJECT DETAILS USER INTERFACE
             Display the currently selected subject
             ================================================= -->

        <div class="ten wide column">
            <div class="ui segment">

                <h3 class="ui header">Subject Details</h3>

                <?php if ($selectedSubject !== ""): ?>

                    <p>
                        <strong>Subject Name:</strong>
                        <?= escapeHTML($selectedSubject) ?>
                    </p>

                    <div class="ui divider"></div>


                    <!-- =====================================
                         SECTION 10: DELETE SUBJECT UI
                         Confirm before deleting the subject
                         ===================================== -->

                    <h4 class="ui header">Delete Subject</h4>

                    <form
                        method="post"
                        onsubmit="return confirm('Are you sure you want to delete this subject?');"
                    >
                        <input
                            type="hidden"
                            name="action"
                            value="delete"
                        >

                        <input
                            type="hidden"
                            name="subjectName"
                            value="<?= escapeHTML($selectedSubject) ?>"
                        >

                        <button class="ui red button" type="submit">
                            Delete <?= escapeHTML($selectedSubject) ?>
                        </button>
                    </form>

                <?php else: ?>

                    <p>
                        Select a subject from the list to view its details.
                    </p>

                <?php endif; ?>

            </div>
        </div>

    </div>

</div>
</body>
</html>
