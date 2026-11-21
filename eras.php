<?php

require_once('config.php');

/*
 Sorting and pagination settings
*/
$allowedSorts = array('EraName', 'EraYears');

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'EraName';
$order = isset($_GET['order']) ? strtoupper($_GET['order']) : 'ASC';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if (!in_array($sort, $allowedSorts)) {
   $sort = 'EraName';
}

if ($order != 'ASC' && $order != 'DESC') {
   $order = 'ASC';
}

if ($page < 1) {
   $page = 1;
}

$perPage = 3;


/*
 Add an era
*/
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {

   try {

      $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $eraName = $_POST['eraName'];
      $eraYears = $_POST['eraYears'];

      $sql = "INSERT INTO Eras (EraName, EraYears)
              VALUES (:eraName, :eraYears)";

      $statement = $pdo->prepare($sql);

      $statement->bindValue(':eraName', $eraName);
      $statement->bindValue(':eraYears', $eraYears);

      $statement->execute();

      $pdo = null;

      header("Location: eras.php");
      exit;
   }

   catch (PDOException $e) {
      die($e->getMessage());
   }
}


/*
 Delete an era
*/
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {

   try {

      $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $eraName = $_POST['eraName'];

      $sql = "DELETE FROM Eras
              WHERE EraName = :eraName";

      $statement = $pdo->prepare($sql);

      $statement->bindValue(':eraName', $eraName);

      $statement->execute();

      $pdo = null;

      header("Location: eras.php");
      exit;
   }

   catch (PDOException $e) {
      die($e->getMessage());
   }
}


/*
 Returns the total number of eras
*/
function getTotalEras() {

   try {

      $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $sql = "SELECT COUNT(*) FROM Eras";
      $result = $pdo->query($sql);
      $total = (int) $result->fetchColumn();

      $pdo = null;

      return $total;
   }

   catch (PDOException $e) {
      die($e->getMessage());
   }
}


/*
 Displays the list of eras in sorted page-by-page order
*/
function outputEras($sort, $order, $page, $perPage) {

   try {

      $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $totalEras = getTotalEras();
      $totalPages = max(1, (int) ceil($totalEras / $perPage));

      if ($page > $totalPages) {
         $page = $totalPages;
      }

      $offset = ($page - 1) * $perPage;

      $sql = "SELECT * FROM Eras
              ORDER BY $sort $order
              LIMIT :limit OFFSET :offset";

      $statement = $pdo->prepare($sql);
      $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
      $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
      $statement->execute();

      while ($row = $statement->fetch()) {

         $params = array(
            'era' => $row['EraName'],
            'sort' => $sort,
            'order' => $order,
            'page' => $page
         );

         echo '<a href="' .
              $_SERVER["SCRIPT_NAME"] .
              '?' .
              http_build_query($params) .
              '" class="';

         if (isset($_GET['era']) &&
             $_GET['era'] == $row['EraName']) {

            echo 'active ';
         }

         echo 'item">';

         echo htmlspecialchars($row['EraName']);

         echo '<div class="description">';
         echo htmlspecialchars($row['EraYears']);
         echo '</div>';

         echo '</a>';
      }

      $pdo = null;
   }

   catch (PDOException $e) {
      die($e->getMessage());
   }
}


/*
 Displays page-by-page navigation
*/
function outputPagination($sort, $order, $page, $perPage) {

   $totalEras = getTotalEras();
   $totalPages = max(1, (int) ceil($totalEras / $perPage));

   if ($page > $totalPages) {
      $page = $totalPages;
   }

   if ($totalPages <= 1) {
      return;
   }

   echo '<div class="ui pagination menu">';

   if ($page > 1) {

      $previousParams = array(
         'sort' => $sort,
         'order' => $order,
         'page' => $page - 1
      );

      echo '<a class="item" href="eras.php?' .
           http_build_query($previousParams) .
           '">Previous</a>';
   }

   for ($i = 1; $i <= $totalPages; $i++) {

      $pageParams = array(
         'sort' => $sort,
         'order' => $order,
         'page' => $i
      );

      echo '<a class="';

      if ($i == $page) {
         echo 'active ';
      }

      echo 'item" href="eras.php?' .
           http_build_query($pageParams) .
           '">' .
           $i .
           '</a>';
   }

   if ($page < $totalPages) {

      $nextParams = array(
         'sort' => $sort,
         'order' => $order,
         'page' => $page + 1
      );

      echo '<a class="item" href="eras.php?' .
           http_build_query($nextParams) .
           '">Next</a>';
   }

   echo '</div>';
}


/*
 Displays selected era details
*/
function outputEraDetails() {

   try {

      if (isset($_GET['era'])) {

         $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS);

         $pdo->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
         );

         $sql = "SELECT * FROM Eras
                 WHERE EraName = :eraName";

         $statement = $pdo->prepare($sql);

         $statement->bindValue(
            ':eraName',
            $_GET['era']
         );

         $statement->execute();

         $row = $statement->fetch();

         if ($row) {

            echo '<div class="ui segment">';

            echo '<h2>';
            echo htmlspecialchars($row['EraName']);
            echo '</h2>';

            echo '<p><strong>Era Years:</strong> ';
            echo htmlspecialchars($row['EraYears']);
            echo '</p>';

            echo '<form method="post"
                  onsubmit="return confirm(\'Are you sure you want to delete this era?\');">';

            echo '<input type="hidden"
                  name="eraName"
                  value="' .
                  htmlspecialchars($row['EraName']) .
                  '">';

            echo '<input type="hidden"
                  name="delete"
                  value="yes">';

            echo '<input type="submit"
                  class="ui red button"
                  value="Delete Era">';

            echo '</form>';

            echo '</div>';
         }

         $pdo = null;
      }
   }

   catch (PDOException $e) {
      die($e->getMessage());
   }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

   <meta charset="utf-8">

   <meta name="viewport"
         content="width=device-width, initial-scale=1.0">

   <title>Art Gallery - Eras</title>

   <link href="semantic/semantic.css"
         rel="stylesheet">

   <style>

      body {
         padding-top: 20px;
         padding-bottom: 40px;
      }

      .sort-buttons {
         margin-top: 24px;
      }

      .pagination-area {
         margin-top: 20px;
      }

      @media (max-width: 767px) {

         body {
            padding-left: 10px;
            padding-right: 10px;
         }

         .sort-buttons {
            margin-top: 10px;
         }
      }

   </style>

</head>


<body>

<main class="ui container">


   <div class="ui secondary segment">

      <h1>Art Gallery - Eras</h1>

   </div>


   <!-- Sorting -->

   <div class="ui segment">

      <h3>Sort Eras</h3>

      <form class="ui form"
            method="get"
            action="eras.php">

         <div class="ui stackable grid">

            <div class="six wide column">

               <div class="field">

                  <label>Sort By</label>

                  <select name="sort"
                          class="ui dropdown">

                     <option value="EraName"
                        <?php if ($sort == 'EraName') echo 'selected'; ?>>
                        Era Name
                     </option>

                     <option value="EraYears"
                        <?php if ($sort == 'EraYears') echo 'selected'; ?>>
                        Era Years
                     </option>

                  </select>

               </div>

            </div>


            <div class="ten wide column sort-buttons">

               <button type="submit"
                       name="order"
                       value="ASC"
                       class="ui primary button">
                  Ascending
               </button>

               <button type="submit"
                       name="order"
                       value="DESC"
                       class="ui button">
                  Descending
               </button>

            </div>

         </div>

      </form>

   </div>


   <!-- Summary report -->

   <div class="ui segment">

      <div class="ui statistic">

         <div class="value">
            <?php echo getTotalEras(); ?>
         </div>

         <div class="label">
            Total Eras
         </div>

      </div>

   </div>


   <div class="ui segment">

      <div class="ui stackable grid">


         <!-- Era list -->

         <div class="five wide column">

            <h3>Eras</h3>

            <div class="ui link list">

               <?php outputEras($sort, $order, $page, $perPage); ?>

            </div>


            <!-- Pagination -->

            <div class="pagination-area">

               <?php outputPagination($sort, $order, $page, $perPage); ?>

            </div>

         </div>


         <!-- Era details -->

         <div class="eleven wide column">

            <h3>Era Details</h3>

            <?php outputEraDetails(); ?>

         </div>


      </div>

   </div>


   <!-- Add Era -->

   <div class="ui segment">

      <h3>Add Era</h3>

      <form class="ui form"
            method="post"
            action="eras.php">

         <div class="field">

            <label>Era Name</label>

            <input type="text"
                   name="eraName"
                   required>

         </div>


         <div class="field">

            <label>Era Years</label>

            <input type="text"
                   name="eraYears"
                   required>

         </div>


         <input type="hidden"
                name="add"
                value="yes">


         <input type="submit"
                class="ui button"
                value="Add Era">

      </form>

   </div>


</main>

</body>

</html>
