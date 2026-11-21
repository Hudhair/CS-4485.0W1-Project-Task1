<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Art Gallery - Admin Home</title>
  <link href="semantic/semantic.css" rel="stylesheet">
  <style>main { padding-top: 20px; padding-bottom: 40px; }</style>
</head>
<body>
<main class="ui container">
  <div class="ui secondary segment"><h1>Art Gallery - Admin Home</h1></div>
  <div class="ui segment">
    <h2 class="ui header">Choose an Admin Option</h2>
    <p>Select whether to add, delete, or update objects in the database.</p>
    <div class="ui three stackable cards">
      <a class="ui card" href="admin-add.php">
        <div class="content"><div class="header">Add Record</div><div class="description">Add a record to the database.</div></div>
      </a>
      <a class="ui card" href="admin-delete.php">
        <div class="content"><div class="header">Delete Record</div><div class="description">Delete a record from the database.</div></div>
      </a>
      <a class="ui card" href="admin-update.php">
        <div class="content"><div class="header">Update Record</div><div class="description">Update a record in the database.</div></div>
      </a>
    </div>
  </div>
</main>
</body>
</html>
