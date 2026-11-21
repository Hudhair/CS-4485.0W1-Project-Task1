
<?php
// Connect to the art database
require_once 'config.php';

$pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Retrieve paintings from the database
$stmt = $pdo->query(
    "SELECT PaintingID, Title, ImageFileName
     FROM Paintings
     ORDER BY PaintingID"
);

$featuredPaintings = [];

// Find five paintings with available images
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $painting) {
    $filename = (string)$painting['ImageFileName'];
    $path = __DIR__ . '/images/art/works/square-medium/' . $filename . '.jpg';

    // Check that the filename is valid and the image exists
    if (
        preg_match('/^[a-zA-Z0-9_-]+$/D', $filename) &&
        is_file($path)
    ) {
        $featuredPaintings[] = [
            'title' => $painting['Title'],
            'image' => 'images/art/works/square-medium/' .
                rawurlencode($filename) . '.jpg'
        ];
    }

    // Stop after finding five paintings
    if (count($featuredPaintings) === 5) {
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Art Gallery - Home</title>
  <link href="semantic/semantic.css" rel="stylesheet">
  <style>main { padding-top: 20px; padding-bottom: 40px; }</style>
</head>
<body>
<main class="ui container">
  <div class="top-bar clearfix" style="overflow: hidden;">
    <a href="login.php" class="ui primary right floated button">Log Out</a>
  </div>
  <div class="ui secondary segment"><h1>Art Gallery - Home</h1></div>
  <div class="ui segment">
    <h2 class="ui header">Browse the Art Gallery</h2>
    <p>Select a section to view records and use the available add and delete features.</p>
    <div class="ui four stackable cards">
      <a class="ui card" href="artist.php">
        <div class="content"><div class="header">Artists</div><div class="description">View, add, and manage artist records.</div></div>
        <div class="extra content">Open Artists</div>
      </a>
      <a class="ui card" href="paintings.php">
        <div class="content"><div class="header">Paintings</div><div class="description">Browse paintings, view details, add paintings, and delete eligible records.</div></div>
        <div class="extra content">Open Paintings</div>
      </a>
      <a class="ui card" href="genres.php">
        <div class="content"><div class="header">Genres</div><div class="description">View, add, and manage genre records.</div></div>
        <div class="extra content">Open Genres</div>
      </a>
      <a class="ui card" href="galleries.php">
        <div class="content">
            <div class="header">Galleries</div>
            <div class="description">View gallery details, add galleries, and manage gallery records.</div>
        </div>
        <div class="extra content">Open Galleries</div>
      </a>
      <a class="ui card" href="eras.php">
        <div class="content">
            <div class="header">Eras</div>
            <div class="description">View era details, add eras, and manage era records.</div>
        </div>
        <div class="extra content">Open Eras</div>
      </a>
      <a class="ui card" href="subjects.php">
        <div class="content">
          <div class="header">Subjects</div>
          <div class="description">
            View subject details, add new subjects, and delete subjects.
          </div>
        </div>
        <div class="extra content">Open Subjects</div>
      </a>
      <a class="ui card" href="art-painting-genres.php">
        <div class="content">
          <div class="header">Painting-Genres</div>
          <div class="description">
            View painting-genre details, add new links between paintings and genres, and delete painting-genre links.
          </div>
        </div>
        <div class="extra content">Open Painting-Genres</div>
      </a>
            <a class="ui card" href="art-painting-subjects.php">
        <div class="content">
          <div class="header">Painting-Subjects</div>
          <div class="description">
            View painting-subject details, add new links between paintings and subjects, and delete painting-subject links.
          </div>
        </div>
        <div class="extra content">Open Painting-Subjects</div>
      </a>
    </div>
  </div>

  <!-- Featured paintings slideshow -->
  <?php if (count($featuredPaintings) > 0): ?>
  <div class="ui segment" style="text-align: center; margin-top: 20px;">
    <h2 class="ui header">Featured Paintings</h2>

    <!-- Display the current painting image -->
    <img
      id="featuredPainting"
      class="ui centered rounded image"
      src="<?= htmlspecialchars($featuredPaintings[0]['image'], ENT_QUOTES, 'UTF-8') ?>"
      alt="<?= htmlspecialchars($featuredPaintings[0]['title'], ENT_QUOTES, 'UTF-8') ?>"
      style="height: 320px; max-width: 100%; object-fit: contain; transition: opacity 0.6s ease-in-out;"
    >

    <!-- Display the painting title -->
    <h3 id="paintingTitle">
      <?= htmlspecialchars($featuredPaintings[0]['title'], ENT_QUOTES, 'UTF-8') ?>
    </h3>
  </div>
  <?php endif; ?>
</main>

<script>
  // Pass painting data from PHP to JavaScript
  const paintings = <?= json_encode(
      $featuredPaintings,
      JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
  ) ?>;

  let currentPainting = 0;

  // Get the image and title elements
  const paintingImage = document.getElementById("featuredPainting");
  const paintingTitle = document.getElementById("paintingTitle");

  // Start the slideshow when multiple paintings are available
  if (paintings.length > 1 && paintingImage && paintingTitle) {

    setInterval(function() {

      // Fade out the current painting
      paintingImage.style.opacity = 0;

      setTimeout(function() {

        // Move to the next painting
        currentPainting = (currentPainting + 1) % paintings.length;

        // Update the image and title
        paintingImage.src = paintings[currentPainting].image;
        paintingImage.alt = paintings[currentPainting].title;
        paintingTitle.textContent = paintings[currentPainting].title;

        // Fade in the next painting
        paintingImage.style.opacity = 1;

      }, 600);

    }, 3000);
  }
</script>
</body>
</html>
