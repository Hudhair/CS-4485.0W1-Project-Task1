
<?php
require_once('config.php');

try {
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Get all galleries for the list.
    $galleryQuery = $pdo->query(
        'SELECT GalleryID, GalleryName, GalleryCity, GalleryCountry
         FROM Galleries
         ORDER BY GalleryName'
    );

    $galleries = $galleryQuery->fetchAll();

    // If a gallery was selected, retrieve its details.
    $selectedGallery = null;
    $galleryId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($galleryId !== false && $galleryId !== null && $galleryId > 0) {
        $detailQuery = $pdo->prepare(
            'SELECT GalleryID, GalleryName, GalleryNativeName,
                    GalleryCity, GalleryCountry, Latitude, Longitude,
                    GalleryWebSite
             FROM Galleries
             WHERE GalleryID = :id'
        );

        $detailQuery->execute([':id' => $galleryId]);
        $selectedGallery = $detailQuery->fetch();
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Unable to connect to the database. Please check your configuration.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Art Galleries</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 24px;
            background: #f5f5f5;
            color: #222;
        }

        main {
            max-width: 1100px;
            margin: 0 auto;
        }

        h1, h2 {
            color: #333;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(250px, 1fr) minmax(300px, 2fr);
            gap: 24px;
            align-items: start;
        }

        .panel {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }

        .gallery-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .gallery-list li {
            border-bottom: 1px solid #eee;
            padding: 12px 0;
        }

        .gallery-list li:last-child {
            border-bottom: none;
        }

        a {
            color: #145a8d;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .selected {
            font-weight: bold;
        }

        .detail-row {
            margin: 12px 0;
        }

        .label {
            font-weight: bold;
        }

        .message {
            color: #666;
        }

        .success-message {
            background: #e6f4ea;
            color: #176b32;
            border: 1px solid #b7dfc2;
            border-radius: 5px;
            padding: 12px;
            margin-bottom: 18px;
        }

        .gallery-actions {
            margin-top: 7px;
            font-size: 0.9em;
        }

        .gallery-actions a {
            margin-right: 12px;
        }

        .add-link {
            display: inline-block;
            margin-bottom: 18px;
            padding: 10px 14px;
            background: #145a8d;
            color: white;
            border-radius: 4px;
        }

        .add-link:hover {
            background: #0d4268;
            text-decoration: none;
        }

        .delete-link {
            color: #a12622;
        }

        @media (max-width: 700px) {
            .layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
<main>
    <h1>Art Galleries</h1>

    <?php if (isset($_GET['deleted']) && $_GET['deleted'] === '1'): ?>
        <div class="success-message">
            Gallery deleted successfully.
        </div>
    <?php endif; ?>

    <a class="add-link" href="gallery-add.php">+ Add Gallery</a>

    <div class="layout">
        <section class="panel">
            <h2>Gallery List</h2>

            <?php if (count($galleries) > 0): ?>
                <ul class="gallery-list">
                    <?php foreach ($galleries as $gallery): ?>
                        <li>
                            <a
                                href="galleries.php?id=<?php echo (int) $gallery['GalleryID']; ?>"
                                class="<?php
                                    echo (
                                        $selectedGallery &&
                                        (int) $selectedGallery['GalleryID'] ===
                                        (int) $gallery['GalleryID']
                                    ) ? 'selected' : '';
                                ?>"
                            >
                                <?php echo htmlspecialchars($gallery['GalleryName'] ?? 'Unnamed gallery'); ?>
                            </a>

                            <div class="message">
                                <?php
                                echo htmlspecialchars($gallery['GalleryCity'] ?? '');
                                if (!empty($gallery['GalleryCountry'])) {
                                    echo ', ' . htmlspecialchars($gallery['GalleryCountry']);
                                }
                                ?>
                            </div>

                            <div class="gallery-actions">
                                <a href="gallery-edit.php?id=<?php echo (int) $gallery['GalleryID']; ?>">
                                    Edit
                                </a>

                                <a
                                    class="delete-link"
                                    href="gallery-delete.php?id=<?php echo (int) $gallery['GalleryID']; ?>"
                                >
                                    Delete
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No galleries were found.</p>
            <?php endif; ?>
        </section>

        <section class="panel">
            <h2>Gallery Details</h2>

            <?php if ($selectedGallery): ?>
                <div class="detail-row">
                    <span class="label">Gallery ID:</span>
                    <?php echo (int) $selectedGallery['GalleryID']; ?>
                </div>

                <div class="detail-row">
                    <span class="label">Name:</span>
                    <?php echo htmlspecialchars($selectedGallery['GalleryName'] ?? ''); ?>
                </div>

                <div class="detail-row">
                    <span class="label">Native Name:</span>
                    <?php echo htmlspecialchars($selectedGallery['GalleryNativeName'] ?? ''); ?>
                </div>

                <div class="detail-row">
                    <span class="label">City:</span>
                    <?php echo htmlspecialchars($selectedGallery['GalleryCity'] ?? ''); ?>
                </div>

                <div class="detail-row">
                    <span class="label">Country:</span>
                    <?php echo htmlspecialchars($selectedGallery['GalleryCountry'] ?? ''); ?>
                </div>

                <div class="detail-row">
                    <span class="label">Latitude:</span>
                    <?php echo htmlspecialchars((string) ($selectedGallery['Latitude'] ?? '')); ?>
                </div>

                <div class="detail-row">
                    <span class="label">Longitude:</span>
                    <?php echo htmlspecialchars((string) ($selectedGallery['Longitude'] ?? '')); ?>
                </div>

                <div class="detail-row">
                    <span class="label">Website:</span>
                    <?php if (!empty($selectedGallery['GalleryWebSite'])): ?>
                        <a
                            href="<?php echo htmlspecialchars($selectedGallery['GalleryWebSite']); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <?php echo htmlspecialchars($selectedGallery['GalleryWebSite']); ?>
                        </a>
                    <?php else: ?>
                        Not provided
                    <?php endif; ?>
                </div>

                <div class="gallery-actions">
                    <a href="gallery-edit.php?id=<?php echo (int) $selectedGallery['GalleryID']; ?>">
                        Edit this gallery
                    </a>

                    <a
                        class="delete-link"
                        href="gallery-delete.php?id=<?php echo (int) $selectedGallery['GalleryID']; ?>"
                    >
                        Delete this gallery
                    </a>
                </div>

            <?php elseif (isset($_GET['id'])): ?>
                <p>That gallery could not be found. Please select a gallery from the list.</p>
            <?php else: ?>
                <p class="message">Select a gallery from the list to view its details.</p>
            <?php endif; ?>
        </section>
    </div>
</main>
</body>
</html>
