
<?php
require_once('config.php');

$pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$message = '';
$messageType = '';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    die('Invalid gallery ID. <a href="galleries.php">Return to gallery list</a>');
}

// Find the gallery.
$stmt = $pdo->prepare('SELECT * FROM Galleries WHERE GalleryID = :id');
$stmt->execute([':id' => $id]);
$gallery = $stmt->fetch();

if (!$gallery) {
    die('Gallery not found. <a href="galleries.php">Return to gallery list</a>');
}

// Only delete after the user confirms.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Check whether paintings reference this gallery.
        $checkStmt = $pdo->prepare(
            'SELECT COUNT(*) FROM Paintings WHERE GalleryID = :id'
        );
        $checkStmt->execute([':id' => $id]);
        $paintingCount = (int)$checkStmt->fetchColumn();

        if ($paintingCount > 0) {
            $message = 'This gallery cannot be deleted because '
                     . $paintingCount
                     . ' painting(s) reference it.';
            $messageType = 'error';
        } else {
            $deleteStmt = $pdo->prepare(
                'DELETE FROM Galleries WHERE GalleryID = :id'
            );
            $deleteStmt->execute([':id' => $id]);

            header('Location: galleries.php?deleted=1');
            exit;
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $message = 'Unable to delete the gallery. Please try again.';
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Gallery</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #222;
            margin: 0;
            padding: 24px;
        }

        main {
            max-width: 650px;
            margin: 0 auto;
        }

        .panel {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 24px;
        }

        .message {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 16px;
        }

        .error {
            background: #fde8e7;
            color: #a12622;
        }

        button {
            margin-top: 18px;
            padding: 11px 18px;
            border: none;
            border-radius: 4px;
            background: #a12622;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background: #7f1d1d;
        }

        a {
            color: #145a8d;
        }
    </style>
</head>

<body>
<main>
    <h1>Delete Gallery</h1>

    <section class="panel">
        <?php if ($message !== ''): ?>
            <div class="message <?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <p>
            Are you sure you want to delete this gallery?
        </p>

        <p>
            <strong>Gallery ID:</strong>
            <?php echo htmlspecialchars((string)$gallery['GalleryID']); ?>
        </p>

        <p>
            <strong>Gallery Name:</strong>
            <?php echo htmlspecialchars($gallery['GalleryName'] ?? ''); ?>
        </p>

        <p>
            The gallery will only be deleted if no paintings reference it.
        </p>

        <form method="post"
              action="gallery-delete.php?id=<?php echo urlencode((string)$id); ?>"
              onsubmit="return confirm('Are you sure you want to delete this gallery?');">

            <button type="submit" name="confirm_delete" value="1">
                Confirm Delete
            </button>
        </form>

        <p><a href="galleries.php">Cancel and return to Gallery List</a></p>
    </section>
</main>
</body>
</html>
