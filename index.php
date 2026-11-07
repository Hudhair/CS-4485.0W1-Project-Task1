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

    </div>
  </div>
</main>
</body>
</html>
