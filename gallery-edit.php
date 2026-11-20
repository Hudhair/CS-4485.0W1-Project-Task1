
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

// Retrieve the gallery.
$stmt = $pdo->prepare('SELECT * FROM Galleries WHERE GalleryID = :id');
$stmt->execute([':id' => $id]);
$gallery = $stmt->fetch();

if (!$gallery) {
    die('Gallery not found. <a href="galleries.php">Return to gallery list</a>');
}

// Populate form values from the existing gallery.
$galleryName = $gallery['GalleryName'] ?? '';
$galleryNativeName = $gallery['GalleryNativeName'] ?? '';
$galleryCity = $gallery['GalleryCity'] ?? '';
$galleryCountry = $gallery['GalleryCountry'] ?? '';
$latitude = $gallery['Latitude'] ?? '';
$longitude = $gallery['Longitude'] ?? '';
$galleryWebsite = $gallery['GalleryWebSite'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $galleryName = trim($_POST['GalleryName'] ?? '');
    $galleryNativeName = trim($_POST['GalleryNativeName'] ?? '');
    $galleryCity = trim($_POST['GalleryCity'] ?? '');
    $galleryCountry = trim($_POST['GalleryCountry'] ?? '');
    $latitude = trim($_POST['Latitude'] ?? '');
    $longitude = trim($_POST['Longitude'] ?? '');
    $galleryWebsite = trim($_POST['GalleryWebSite'] ?? '');

    if ($galleryName === '') {
        $message = 'Gallery name is required.';
        $messageType = 'error';
    } elseif (
        ($latitude !== '' && !is_numeric($latitude)) ||
        ($longitude !== '' && !is_numeric($longitude))
    ) {
        $message = 'Latitude and longitude must be numeric values.';
        $messageType = 'error';
    } elseif (
        ($latitude !== '' && ((float)$latitude < -90 || (float)$latitude > 90)) ||
        ($longitude !== '' && ((float)$longitude < -180 || (float)$longitude > 180))
    ) {
        $message = 'Latitude must be between -90 and 90, and longitude between -180 and 180.';
        $messageType = 'error';
    } elseif (
        $galleryWebsite !== '' &&
        !filter_var($galleryWebsite, FILTER_VALIDATE_URL)
    ) {
        $message = 'Please enter a valid website URL, including http:// or https://.';
        $messageType = 'error';
    } else {
        try {
            $sql = 'UPDATE Galleries
                    SET GalleryName = :name,
                        GalleryNativeName = :nativeName,
                        GalleryCity = :city,
                        GalleryCountry = :country,
                        Latitude = :latitude,
                        Longitude = :longitude,
                        GalleryWebSite = :website
                    WHERE GalleryID = :id';

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $galleryName,
                ':nativeName' => $galleryNativeName !== '' ? $galleryNativeName : null,
                ':city' => $galleryCity !== '' ? $galleryCity : null,
                ':country' => $galleryCountry !== '' ? $galleryCountry : null,
                ':latitude' => $latitude !== '' ? $latitude : null,
                ':longitude' => $longitude !== '' ? $longitude : null,
                ':website' => $galleryWebsite !== '' ? $galleryWebsite : null,
                ':id' => $id
            ]);

            $message = 'Gallery updated successfully.';
            $messageType = 'success';

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $message = 'Unable to update the gallery. Please try again.';
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gallery</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #222;
            margin: 0;
            padding: 24px;
        }

        main {
            max-width: 700px;
            margin: 0 auto;
        }

        .panel {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 24px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 16px;
        }

        input {
            box-sizing: border-box;
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #bbb;
            border-radius: 4px;
        }

        button {
            margin-top: 22px;
            padding: 11px 18px;
            border: none;
            border-radius: 4px;
            background: #145a8d;
            color: white;
            cursor: pointer;
        }

        .message {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 16px;
        }

        .success {
            background: #e6f4ea;
            color: #176b32;
        }

        .error {
            background: #fde8e7;
            color: #a12622;
        }
    </style>
</head>

<body>
<main>
    <h1>Edit Gallery</h1>
    <p>Gallery ID: <?php echo htmlspecialchars((string)$id); ?></p>

    <section class="panel">
        <?php if ($message !== ''): ?>
            <div class="message <?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="gallery-edit.php?id=<?php echo urlencode((string)$id); ?>">
            <label for="GalleryName">Gallery Name *</label>
            <input type="text" id="GalleryName" name="GalleryName"
                   maxlength="255" required
                   value="<?php echo htmlspecialchars($galleryName); ?>">

            <label for="GalleryNativeName">Native Name</label>
            <input type="text" id="GalleryNativeName" name="GalleryNativeName"
                   maxlength="255"
                   value="<?php echo htmlspecialchars($galleryNativeName); ?>">

            <label for="GalleryCity">City</label>
            <input type="text" id="GalleryCity" name="GalleryCity"
                   maxlength="255"
                   value="<?php echo htmlspecialchars($galleryCity); ?>">

            <label for="GalleryCountry">Country</label>
            <input type="text" id="GalleryCountry" name="GalleryCountry"
                   maxlength="255"
                   value="<?php echo htmlspecialchars($galleryCountry); ?>">

            <label for="Latitude">Latitude</label>
            <input type="number" id="Latitude" name="Latitude"
                   step="any" min="-90" max="90"
                   value="<?php echo htmlspecialchars((string)$latitude); ?>">

            <label for="Longitude">Longitude</label>
            <input type="number" id="Longitude" name="Longitude"
                   step="any" min="-180" max="180"
                   value="<?php echo htmlspecialchars((string)$longitude); ?>">

            <label for="GalleryWebSite">Website</label>
            <input type="url" id="GalleryWebSite" name="GalleryWebSite"
                   maxlength="255" placeholder="https://example.com"
                   value="<?php echo htmlspecialchars($galleryWebsite); ?>">

            <button type="submit">Save Changes</button>
        </form>

        <p><a href="galleries.php">Back to Gallery List</a></p>
    </section>
</main>
</body>
</html>
