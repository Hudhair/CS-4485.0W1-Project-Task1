<?php
require_once 'config.php';

$pdo = null;
$artist_error = '';
$artist_success = '';

function create_connection() {
    global $pdo;
    if ($pdo instanceof PDO) return $pdo;
    $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    return $pdo;
}

function artist_h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function artist_id() {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    return ($id && $id > 0) ? $id : null;
}

function artist_validate_year($raw, $label) {
    $raw = trim((string)$raw);
    if ($raw === '') return null;
    $year = filter_var($raw, FILTER_VALIDATE_INT);
    $currentYear = (int)date('Y');
    if ($year === false || $year < 0 || $year > $currentYear || ($year !== 0 && $year < 1000)) {
        throw new RuntimeException("$label must be 0 (unknown) or a whole year between 1000 and $currentYear.");
    }
    return $year;
}

function artist_validate_text($raw, $label, $maxLength, $required = false) {
    $value = trim((string)$raw);
    if ($required && $value === '') throw new RuntimeException("$label is required.");
    if (strlen($value) > $maxLength) throw new RuntimeException("$label cannot exceed $maxLength characters.");
    return $value === '' ? null : $value;
}

function artist_validate_link($raw) {
    $value = artist_validate_text($raw, 'Artist link', 255);
    if ($value !== null && (!filter_var($value, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//i', $value))) {
        throw new RuntimeException('Artist link must be a valid URL beginning with http:// or https://.');
    }
    return $value;
}

function link_artists() {
    try {
        $stmt = create_connection()->query('SELECT ArtistID, FirstName, LastName FROM artists ORDER BY LastName, FirstName');
        while ($row = $stmt->fetch()) {
            $id = (int)$row['ArtistID'];
            $name = trim(($row['FirstName'] ?? '') . ' ' . ($row['LastName'] ?? ''));
            $classes = 'item' . (isset($_GET['id']) && (string)$_GET['id'] === (string)$id ? ' active' : '');
            $goto = artist_h($_SERVER['SCRIPT_NAME'] . '?id=' . $id);
            echo '<a href="' . $goto . '" class="' . $classes . '">' . artist_h($name) . '</a>';
        }
    } catch (PDOException $e) {
        echo '<p class="ui negative message">Unable to load artists. Please check the database connection.</p>';
    }
}

function get_artist($artist_id) {
    $stmt = create_connection()->prepare('SELECT * FROM artists WHERE ArtistID = ?');
    $stmt->execute([(int)$artist_id]);
    return $stmt->fetch() ?: null;
}

function edit_artist() {
    global $artist_error, $artist_success;
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_GET['edit'])) return;

    $allowed = ['FirstName', 'LastName', 'Nationality', 'Gender', 'YearOfBirth', 'YearOfDeath', 'Details', 'ArtistLink'];
    $column = (string)($_POST['column'] ?? '');
    if (!in_array($column, $allowed, true)) {
        $artist_error = 'Select a valid artist field to edit.';
        return;
    }
    $id = artist_id();
    if ($id === null) {
        $artist_error = 'Select a valid artist before editing.';
        return;
    }

    try {
        $raw = $_POST['value'] ?? '';
        if ($column === 'YearOfBirth' || $column === 'YearOfDeath') {
            $value = artist_validate_year($raw, $column === 'YearOfBirth' ? 'Year of birth' : 'Year of death');
            $existing = get_artist($id);
            $birth = $column === 'YearOfBirth' ? $value : artist_validate_year($existing['YearOfBirth'] ?? '', 'Year of birth');
            $death = $column === 'YearOfDeath' ? $value : artist_validate_year($existing['YearOfDeath'] ?? '', 'Year of death');
            if ($birth !== null && $birth !== 0 && $death !== null && $death !== 0 && $death < $birth) {
                throw new RuntimeException('Year of death cannot be earlier than year of birth.');
            }
        } elseif ($column === 'ArtistLink') {
            $value = artist_validate_link($raw);
        } elseif ($column === 'Gender') {
            $value = artist_validate_text($raw, 'Gender', 1);
        } elseif ($column === 'FirstName' || $column === 'LastName') {
            $value = artist_validate_text($raw, $column === 'FirstName' ? 'First name' : 'Last name', 50, $column === 'LastName');
        } elseif ($column === 'Nationality') {
            $value = artist_validate_text($raw, 'Nationality', 50);
        } else {
            $value = artist_validate_text($raw, 'Details', 100000);
        }

        if ($column === 'LastName' && $value === null) throw new RuntimeException('Last name is required.');
        $stmt = create_connection()->prepare("UPDATE artists SET `$column` = ? WHERE ArtistID = ?");
        $stmt->execute([$value, $id]);
        $artist_success = 'Artist information updated successfully.';
    } catch (Throwable $e) {
        $artist_error = $e instanceof PDOException ? 'Unable to update the artist. Please check the entered values.' : $e->getMessage();
    }
}

function delete_artist() {
    if (!isset($_GET['id']) || !isset($_GET['delete'])) return;
    $id = artist_id();
    if ($id === null) return;
    try {
        $db = create_connection();
        $db->beginTransaction();
        $stmt = $db->prepare('UPDATE paintings SET ArtistID = NULL WHERE ArtistID = ?');
        $stmt->execute([$id]);
        $stmt = $db->prepare('DELETE FROM artists WHERE ArtistID = ?');
        $stmt->execute([$id]);
        $db->commit();
        header('Location: artist.php');
        exit;
    } catch (Throwable $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        http_response_code(500);
        exit('Unable to delete this artist.');
    }
}

function create_artist() {
    global $artist_error, $artist_success;
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_GET['create'])) return;
    try {
        $first = artist_validate_text($_POST['FirstName'] ?? '', 'First name', 50);
        $last = artist_validate_text($_POST['LastName'] ?? '', 'Last name', 50, true);
        $nationality = artist_validate_text($_POST['Nationality'] ?? '', 'Nationality', 50);
        $gender = artist_validate_text($_POST['Gender'] ?? '', 'Gender', 1);
        $birth = artist_validate_year($_POST['YearOfBirth'] ?? '', 'Year of birth');
        $death = artist_validate_year($_POST['YearOfDeath'] ?? '', 'Year of death');
        if ($birth !== null && $birth !== 0 && $death !== null && $death !== 0 && $death < $birth) {
            throw new RuntimeException('Year of death cannot be earlier than year of birth.');
        }
        $details = artist_validate_text($_POST['Details'] ?? '', 'Details', 100000);
        $link = artist_validate_link($_POST['ArtistLink'] ?? '');

        $stmt = create_connection()->prepare('INSERT INTO artists (FirstName, LastName, Nationality, Gender, YearOfBirth, YearOfDeath, Details, ArtistLink) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$first, $last, $nationality, $gender, $birth, $death, $details, $link]);
        $artist_success = 'Artist added successfully.';
    } catch (Throwable $e) {
        $artist_error = $e instanceof PDOException ? 'Unable to add the artist. Please check the entered values.' : $e->getMessage();
    }
}

function print_artist($row) {
    $name = '[' . (int)$row['ArtistID'] . '] ' . trim(($row['FirstName'] ?? '') . ' ' . ($row['LastName'] ?? ''));
    echo '<div class="item"><div class="content"><h4 class="header">' . artist_h($name) . '</h4><div class="description">';
    foreach ([
        'Nationality' => $row['Nationality'], 'Gender' => $row['Gender'],
        'Year of Birth' => $row['YearOfBirth'], 'Year of Death' => $row['YearOfDeath'],
        'Details' => $row['Details'], 'Artist Link' => $row['ArtistLink']
    ] as $label => $value) {
        echo '<p><strong>' . artist_h($label) . ':</strong> ' . artist_h($value) . '</p>';
    }
    echo '</div></div></div>';
}

function form_create_artist() {
    $id = artist_id() ?? 0;
    $values = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['create']) ? $_POST : [];
    echo '<h4 class="header">--- Create an Artist ---</h4>';
    echo '<form id="createArtistForm" class="ui form" action="' . artist_h($_SERVER['SCRIPT_NAME'] . '?id=' . $id . '&create=1') . '" method="post" novalidate>';
    $fields = [
        'FirstName' => ['First Name', 'text'], 'LastName' => ['Last Name *', 'text'],
        'Nationality' => ['Nationality', 'text'], 'Gender' => ['Gender (one character)', 'text'],
        'YearOfBirth' => ['Year of Birth', 'number'], 'YearOfDeath' => ['Year of Death', 'number'],
        'Details' => ['Details', 'text'], 'ArtistLink' => ['Artist Link (https://...)', 'url']
    ];
    foreach ($fields as $key => [$label, $type]) {
        $value = artist_h($values[$key] ?? '');
        $max = $key === 'FirstName' || $key === 'LastName' || $key === 'Nationality' ? ' maxlength="50"' : ($key === 'Gender' ? ' maxlength="1"' : '');
        $range = in_array($key, ['YearOfBirth', 'YearOfDeath'], true) ? ' min="0" max="' . date('Y') . '" step="1"' : '';
        echo '<div class="field"><label for="' . $key . '">' . artist_h($label) . '</label><input type="' . $type . '" id="' . $key . '" name="' . $key . '" value="' . $value . '"' . $max . $range . '></div>';
    }
    echo '<button class="ui primary button" type="submit">Add Artist</button></form>';
}

function form_edit_artist() {
    $id = artist_id();
    if ($id === null) return;
    $fields = ['FirstName'=>'First Name','LastName'=>'Last Name','Nationality'=>'Nationality','Gender'=>'Gender','YearOfBirth'=>'Year of Birth','YearOfDeath'=>'Year of Death','Details'=>'Details','ArtistLink'=>'Artist Link'];
    echo '<form id="editArtistForm" class="ui form" action="' . artist_h($_SERVER['SCRIPT_NAME'] . '?id=' . $id . '&edit=1') . '" method="post" novalidate>';
    echo '<div class="field"><label for="column">Edit:</label><select id="column" name="column" required><option value="">Select a field</option>';
    foreach ($fields as $key => $label) echo '<option value="' . artist_h($key) . '">' . artist_h($label) . '</option>';
    echo '</select></div><div class="field"><label for="value">New value:</label><input type="text" id="value" name="value" required></div><button class="ui button" type="submit">Save Changes</button></form>';
}

function form_delete_artist($row) {
    $id = (int)$row['ArtistID'];
    $name = trim(($row['FirstName'] ?? '') . ' ' . ($row['LastName'] ?? ''));
    echo '<br><br><a href="' . artist_h($_SERVER['SCRIPT_NAME'] . '?id=' . $id . '&delete=1') . '" class="ui button" onclick="return confirm(\'Delete ' . artist_h($name) . '?\')">Delete ' . artist_h($name) . '</a>';
}

function form_artist() {
    $id = artist_id();
    if ($id === null) return;
    $row = get_artist($id);
    if (!$row) { echo '<p class="ui warning message">Artist not found.</p>'; return; }
    print_artist($row);
    form_edit_artist();
    form_delete_artist($row);
}

edit_artist();
delete_artist();
create_artist();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Art Gallery - Artists</title>
<link href="semantic/semantic.css" rel="stylesheet">
</head>
<body>
<main class="ui container">
    <div class="ui secondary segment">
        <h1>Art Gallery - Artists</h1>
        <div class="ui small horizontal link list page-links">
            <a class="item" href="index.php">Home</a><a class="item" href="artist.php">Artists</a>
            <a class="item" href="paintings.php">Paintings</a><a class="item" href="genres.php">Genres</a>
            <a class="item" href="eras.php">Eras</a><a class="item" href="galleries.php">Galleries</a>
            <a class="item" href="subjects.php">Subjects</a><a class="item" href="art-painting-genres.php">Painting Genres</a>
            <a class="item" href="art-painting-subjects.php">Painting Subjects</a>
        </div>
    </div>
    <?php if ($artist_error !== ''): ?><div class="ui negative message"><?= artist_h($artist_error) ?></div><?php endif; ?>
    <?php if ($artist_success !== ''): ?><div class="ui positive message"><?= artist_h($artist_success) ?></div><?php endif; ?>
    <div class="ui segment"><div class="ui segment"><?php form_create_artist(); ?></div>
        <div class="ui grid"><div class="four wide column"><div class="ui link list"><?php link_artists(); ?></div></div>
            <div class="twelve wide column"><div class="ui items"><?php form_artist(); ?></div></div></div>
    </div>
</main>
<script>
(function () {
    const currentYear = new Date().getFullYear();
    function validateArtistForm(form, isCreate) {
        form.addEventListener('submit', function (event) {
            const errors = [];
            if (isCreate) {
                const lastName = form.elements.LastName.value.trim();
                if (!lastName) errors.push('Last name is required.');
                const birthText = form.elements.YearOfBirth.value.trim();
                const deathText = form.elements.YearOfDeath.value.trim();
                const birth = birthText === '' ? null : Number(birthText);
                const death = deathText === '' ? null : Number(deathText);
                for (const [label, year] of [['Year of birth', birth], ['Year of death', death]]) {
                    if (year !== null && (!Number.isInteger(year) || year < 1000 || year > currentYear)) errors.push(label + ' must be a whole year between 1000 and ' + currentYear + '.');
                }
                if (birth !== null && death !== null && death < birth) errors.push('Year of death cannot be earlier than year of birth.');
                const gender = form.elements.Gender.value.trim();
                if (gender.length > 1) errors.push('Gender must be one character.');
                const link = form.elements.ArtistLink.value.trim();
                if (link && !/^https?:\/\//i.test(link)) errors.push('Artist link must begin with http:// or https://.');
            } else {
                const field = form.elements.column.value;
                const value = form.elements.value.value.trim();
                if (!field) errors.push('Select a field to edit.');
                if (!value) errors.push('Enter a value.');
                if ((field === 'YearOfBirth' || field === 'YearOfDeath') && value) {
                    const year = Number(value);
                    if (!Number.isInteger(year) || year < 0 || year > currentYear || (year !== 0 && year < 1000)) errors.push('Enter 0 (unknown) or a valid year between 1000 and ' + currentYear + '.');
                }
                if (field === 'Gender' && value.length > 1) errors.push('Gender must be one character.');
                if (field === 'ArtistLink' && value && !/^https?:\/\//i.test(value)) errors.push('Artist link must begin with http:// or https://.');
            }
            if (errors.length) { event.preventDefault(); alert(errors.join('\n')); }
        });
    }
    const createForm = document.getElementById('createArtistForm');
    const editForm = document.getElementById('editArtistForm');
    if (createForm) validateArtistForm(createForm, true);
    if (editForm) validateArtistForm(editForm, false);
    if (editForm) {
        editForm.elements.column.addEventListener('change', function () {
            const input = editForm.elements.value;
            if (this.value === 'YearOfBirth' || this.value === 'YearOfDeath') { input.type = 'number'; input.min = '0'; input.max = String(currentYear); input.step = '1'; }
            else if (this.value === 'ArtistLink') { input.type = 'url'; input.removeAttribute('min'); input.removeAttribute('max'); input.removeAttribute('step'); }
            else { input.type = 'text'; input.removeAttribute('min'); input.removeAttribute('max'); input.removeAttribute('step'); }
        });
    }
})();
</script>
</body>
</html>
