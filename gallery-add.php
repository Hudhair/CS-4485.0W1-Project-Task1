
<?php
require_once('config.php');

$message = '';
$messageType = '';

$galleryName = '';
$galleryNativeName = '';
$galleryCity = '';
$galleryCountry = '';
$latitude = '';
$longitude = '';
$galleryWebsite = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Keep submitted values in the form if validation fails.
    $galleryName = trim($_POST['GalleryName'] ?? '');
    $galleryNativeName = trim($_POST['GalleryNativeName'] ?? '');
    $galleryCity = trim($_POST['GalleryCity'] ?? '');
    $galleryCountry = trim($_POST['GalleryCountry'] ?? '');
    $latitude = trim($_POST['Latitude'] ?? '');
    $longitude = trim($_POST['Longitude'] ?? '');
    $galleryWebsite = trim($_POST['GalleryWebSite'] ?? '');

    // GalleryName is required.
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
            $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = 'INSERT INTO Galleries
                    (GalleryName, GalleryNativeName, GalleryCity, GalleryCountry,
                     Latitude, Longitude, GalleryWebSite)
                    VALUES
                    (:name, :nativeName, :city, :country, :latitude, :longitude, :website)';

            $statement = $pdo->prepare($sql);

            $statement->execute([
                ':name' => $galleryName,
                ':nativeName' => $galleryNativeName !== '' ? $galleryNativeName : null,
                ':city' => $galleryCity !== '' ? $galleryCity : null,
                ':country' => $galleryCountry !== '' ? $galleryCountry : null,
                ':latitude' => $latitude !== '' ? $latitude : null,
                ':longitude' => $longitude !== '' ? $longitude : null,
                ':website' => $galleryWebsite !== '' ? $galleryWebsite : null
            ]);

            $newGalleryId = $pdo->lastInsertId();

            $message = 'Gallery added successfully! New Gallery ID: ' . $newGalleryId;
            $messageType = 'success';

            // Clear the form after successful insertion.
            $galleryName = '';
            $galleryNativeName = '';
            $galleryCity = '';
            $galleryCountry = '';
            $latitude = '';
            $longitude = '';
            $galleryWebsite = '';

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $message = 'Unable to add the gallery. Please check the database and try again.';
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
    <title>Add Gallery</title>

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

        button:hover {
            background: #0d4268;
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

        .links {
            margin-top: 18px;
        }

        a {
            color: #145a8d;
        }
    </style>
</head>

<body>
<main>
    <h1>Add a Gallery</h1>

    <section class="panel">

        <?php if ($message !== ''): ?>
            <div class="message <?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="gallery-add.php">
            <label for="GalleryName">Gallery Name *</label>
            <input
                type="text"
                id="GalleryName"
                name="GalleryName"
                maxlength="255"
                required
                value="<?php echo htmlspecialchars($galleryName); ?>"
            >

            <label for="GalleryNativeName">Native Name</label>
            <input
                type="text"
                id="GalleryNativeName"
                name="GalleryNativeName"
                maxlength="255"
                value="<?php echo htmlspecialchars($galleryNativeName); ?>"
            >

            <label for="GalleryCity">City</label>
            <input
                type="text"
                id="GalleryCity"
                name="GalleryCity"
                maxlength="255"
                value="<?php echo htmlspecialchars($galleryCity); ?>"
            >

            <label for="GalleryCountry">Country</label>
            <input
                type="text"
                id="GalleryCountry"
                name="GalleryCountry"
                maxlength="255"
                value="<?php echo htmlspecialchars($galleryCountry); ?>"
            >

            <label for="Latitude">Latitude</label>
            <input
                type="number"
                id="Latitude"
                name="Latitude"
                step="any"
                min="-90"
                max="90"
                value="<?php echo htmlspecialchars($latitude); ?>"
            >

            <label for="Longitude">Longitude</label>
            <input
                type="number"
                id="Longitude"
                name="Longitude"
                step="any"
                min="-180"
                max="180"
                value="<?php echo htmlspecialchars($longitude); ?>"
            >

            <label for="GalleryWebSite">Website</label>
            <input
                type="url"
                id="GalleryWebSite"
                name="GalleryWebSite"
                maxlength="255"
                placeholder="https://example.com"
                value="<?php echo htmlspecialchars($galleryWebsite); ?>"
            >

            <button type="submit">Add Gallery</button>
        </form>

        <div class="links">
            <a href="galleries.php">Back to Gallery List</a>
        </div>

    </section>
</main>
</body>
</html>
