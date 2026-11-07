<?php
// helper for Paintings to add/delete/edit/view
require_once __DIR__ . '/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['paintings_csrf'])) $_SESSION['paintings_csrf'] = bin2hex(random_bytes(32));
function painting_h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function painting_url($id = null) {
    return 'paintings.php' . ($id !== null ? '?id=' . (int)$id : '');
}
function painting_exists(PDO $db, $table, $key, $id) {
    $stmt = $db->prepare("SELECT 1 FROM `$table` WHERE `$key` = ? LIMIT 1");
    $stmt->execute([$id]);
    return (bool)$stmt->fetchColumn();
}
function painting_count(PDO $db, $table, $id) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM `$table` WHERE PaintingID = ?");
    $stmt->execute([$id]);
    return (int)$stmt->fetchColumn();
}
function painting_optional_int($value, $label, $min = 0) {
    if ($value === '' || $value === null) return null;
    if (!ctype_digit((string)$value) || (float)$value > PHP_INT_MAX || (int)$value < $min) {
        throw new RuntimeException("$label must be a valid number.");
    }
    return (int)$value;
}
function painting_fields(PDO $db, $post, $isNew) {
    $values = [];
    $textFields = [
        'Title' => 255, 'ImageFileName' => 50, 'MuseumLink' => 255,
        'AccessionNumber' => 255, 'CopyrightText' => 255,
        'Description' => null, 'Excerpt' => null, 'Medium' => 255,
        'GoogleLink' => 255, 'GoogleDescription' => null, 'WikiLink' => 255
    ];
    foreach ($textFields as $key => $max) {
        $s = trim((string)($post[$key] ?? ''));
        if ($max !== null && strlen($s) > $max) {
            throw new RuntimeException("$key exceeds $max bytes.");
        }
        $values[$key] = $s === '' ? null : $s;
    }
    if ($values['Title'] === null) throw new RuntimeException('Painting title is required.');
    if ($values['ImageFileName'] === null || !preg_match('/^[a-zA-Z0-9_-]+$/D', $values['ImageFileName'])) {
        throw new RuntimeException('Image filename is required: enter the name without .jpg (letters, numbers, underscores, hyphens).');
    }
    foreach (['ArtistID' => ['Artists','ArtistID'], 'GalleryID' => ['Galleries','GalleryID']] as $key => $ref) {
        $id = painting_optional_int($post[$key] ?? '', $key, 1);
        if ($id !== null && !painting_exists($db, $ref[0], $ref[1], $id)) {
            throw new RuntimeException("$key $id does not exist in the {$ref[0]} table.");
        }
        $values[$key] = $id;
    }
    
    $shape = painting_optional_int($post['ShapeID'] ?? '', 'ShapeID', 1);
    $shapeCount = (int)$db->query('SELECT COUNT(*) FROM Shapes')->fetchColumn();
    if ($shape !== null && $shape > $shapeCount) throw new RuntimeException('ShapeID is not listed in Shapes.');
    $values['ShapeID'] = $shape;
    
    foreach (['YearOfWork','Width','Height'] as $key) {
        $values[$key] = painting_optional_int($post[$key] ?? '', $key);
    }
    foreach (['Cost','MSRP'] as $key) {
        $raw = trim((string)($post[$key] ?? ''));
        if ($raw === '') { $values[$key] = null; continue; }
        if (!preg_match('/^\d{1,15}(?:\.\d{1,4})?$/D', $raw)) {
            throw new RuntimeException("$key must be a nonnegative amount with up to four decimal places.");
        }
        $values[$key] = $raw;
    }
    return $values;
}

function painting_db() {
    try {
        return new PDO(DBCONNSTRING, DBUSER, DBPASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        exit('Could not connect to the art database. Check config.php and MySQL.');
    }
}
function painting_id() {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    return ($id && $id > 0) ? $id : null;
}
function painting_require_csrf() {
    if (!hash_equals($_SESSION['paintings_csrf'], (string)($_POST['csrf'] ?? ''))) {
        throw new RuntimeException('Invalid form request. Reload the page and try again.');
    }
}
function painting_find($db, $id) {
    $stmt = $db->prepare('SELECT p.*, a.FirstName, a.LastName, g.GalleryName FROM Paintings p LEFT JOIN Artists a ON a.ArtistID = p.ArtistID LEFT JOIN Galleries g ON g.GalleryID = p.GalleryID WHERE p.PaintingID = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
function painting_choices($db) {
    $artists = $db->query('SELECT ArtistID, FirstName, LastName FROM Artists ORDER BY LastName, FirstName')->fetchAll();
    $galleries = $db->query('SELECT GalleryID, GalleryName FROM Galleries ORDER BY GalleryName')->fetchAll();
    $shapes = $db->query('SELECT ShapeName FROM Shapes')->fetchAll();
    foreach ($shapes as $i => &$shape) $shape['ShapeID'] = $i + 1;
    unset($shape);
    return [$artists, $galleries, $shapes];
}
function painting_save($db, $post, $isNew) {
    $fields = painting_fields($db, $post, $isNew);
    $target = null;
    if (!$isNew) {
        $target = painting_optional_int($post['PaintingID'] ?? '', 'PaintingID', 1);
        if ($target === null || !painting_exists($db, 'Paintings', 'PaintingID', $target)) {
            throw new RuntimeException('Painting not found.');
        }
    }
    $uploadedFile = null;
    $file = $_FILES['image'] ?? null;
    if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Image upload must be a JPEG up to 5 MB.');
        }
        $info = @getimagesize($file['tmp_name']);
        if (!$info || $info[2] !== IMAGETYPE_JPEG) throw new RuntimeException('Only actual JPEG images are accepted.');
        $imageDir = __DIR__ . '/images/art/works/square-medium';
        if (!is_dir($imageDir)) throw new RuntimeException('Images folder is missing.');
        $uploadedFile = $imageDir . '/' . $fields['ImageFileName'] . '.jpg';
        if (file_exists($uploadedFile)) throw new RuntimeException('An image already uses this filename. Choose another filename.');
        if (!move_uploaded_file($file['tmp_name'], $uploadedFile)) throw new RuntimeException('Could not save the image.');
    }
    try {
        if ($isNew) {
            $cols = array_keys($fields);
            $stmt = $db->prepare('INSERT INTO Paintings (`' . implode('`,`', $cols) . '`) VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')');
            $stmt->execute(array_values($fields));
            $target = (int)$db->lastInsertId();
        } else {
            // Do not update the primary key PaintingID.
            $sets = array_map(fn($name) => '`' . $name . '` = ?', array_keys($fields));
            $stmt = $db->prepare('UPDATE Paintings SET ' . implode(', ', $sets) . ' WHERE PaintingID = ?');
            $stmt->execute(array_merge(array_values($fields), [$target]));
        }
    } catch (Throwable $e) {
        if ($uploadedFile !== null && is_file($uploadedFile)) unlink($uploadedFile);
        throw $e;
    }
    return $target;
}
function painting_error($e) {
    return $e instanceof PDOException
        ? ($e->getCode() === '23000' ? 'Cannot save: image filename must be unique and references must be valid.' : 'Database operation failed. Check permissions and table structure.')
        : $e->getMessage();
}
function painting_header($title) { ?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= painting_h($title) ?> - Art Gallery</title>
<link rel="stylesheet" href="semantic/semantic.css">
<style>main{padding-top:20px;padding-bottom:40px}.painting-list{max-height:68vh;overflow-y:auto}.painting-image{max-width:320px;max-height:320px;object-fit:contain}.painting-detail{overflow-wrap:anywhere}.painting-form{max-width:900px}.page-links{margin-top:.75rem}</style>
</head><body><main class="ui container">
<div class="ui secondary segment"><h1>Art Gallery - <?= painting_h($title) ?></h1>
<div class="ui small horizontal link list page-links">
<a class="item" href="index.php">Home</a>
<a class="item" href="artist.php">Artists</a>
<a class="item" href="paintings.php">Paintings</a>
<a class="item" href="genres.php">Genres</a>
<a class="item" href="eras.php">Eras</a>
<a class="item" href="galleries.php">Galleries</a>
<a class="item" href="subjects.php">Subjects</a>
<a class="item" href="art-painting-genres.php">Painting Genres</a>
<a class="item" href="art-painting-subjects.php">Painting Subjects</a>
</div></div>
<?php }
function painting_footer() { echo '</main></body></html>'; }
function painting_form($db, $record, $edit, $id, $post = []) {
    [$artists, $galleries, $shapes] = painting_choices($db);
    $formFields = [
       'ImageFileName'=>'Image filename (without .jpg)', 'Title'=>'Title', 'YearOfWork'=>'Year of work',
       'Width'=>'Width', 'Height'=>'Height', 'Medium'=>'Medium', 'Cost'=>'Cost', 'MSRP'=>'MSRP',
       'MuseumLink'=>'Museum link', 'AccessionNumber'=>'Accession number', 'CopyrightText'=>'Copyright text',
       'Description'=>'Description', 'Excerpt'=>'Excerpt', 'GoogleLink'=>'Google link',
       'GoogleDescription'=>'Google description', 'WikiLink'=>'Wiki link'
    ];
    $values = $post ?: $record;
    ?>
    <p>PaintingID is assigned automatically and cannot be modified. Choose an existing artist, gallery and shape.</p>
    <form class="ui form painting-form" method="post" action="<?= $edit ? 'painting-edit.php?id='.(int)$id : 'painting-add.php' ?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= painting_h($_SESSION['paintings_csrf']) ?>">
    <?php if ($edit): ?><input type="hidden" name="PaintingID" value="<?= (int)$id ?>"><?php endif; ?>
    <div class="three fields">
    <?php foreach ([['ArtistID','Artist','ArtistID',$artists],['GalleryID','Gallery','GalleryID',$galleries],['ShapeID','Shape','ShapeID',$shapes]] as $choice): ?>
      <div class="field"><label for="<?= $choice[0] ?>"><?= $choice[1] ?></label>
      <select name="<?= $choice[0] ?>" id="<?= $choice[0] ?>"><option value="">None</option>
      <?php foreach ($choice[3] as $option):
        $label = $choice[0] === 'ArtistID' ? trim(($option['FirstName'] ?? '').' '.($option['LastName'] ?? '')) : ($choice[0] === 'GalleryID' ? $option['GalleryName'] : $option['ShapeName']); ?>
      <option value="<?= (int)$option[$choice[2]] ?>" <?= (string)($values[$choice[0]] ?? '') === (string)$option[$choice[2]] ? 'selected' : '' ?>><?= painting_h($label) ?> (#<?= (int)$option[$choice[2]] ?>)</option>
      <?php endforeach; ?></select></div>
    <?php endforeach; ?></div>
    <?php foreach ($formFields as $key => $label): ?>
    <div class="field"><label for="<?= $key ?>"><?= painting_h($label) ?><?= in_array($key,['Title','ImageFileName'],true) ? ' *' : '' ?></label>
    <?php if (in_array($key,['Description','Excerpt','GoogleDescription'],true)): ?>
      <textarea rows="3" name="<?= $key ?>" id="<?= $key ?>"><?= painting_h($values[$key] ?? '') ?></textarea>
    <?php else: ?>
      <input type="<?= in_array($key,['YearOfWork','Width','Height'],true) ? 'number' : 'text' ?>" name="<?= $key ?>" id="<?= $key ?>"
      value="<?= painting_h($values[$key] ?? '') ?>" <?= in_array($key,['Title','ImageFileName'],true) ? 'required' : '' ?>
      <?= in_array($key,['YearOfWork','Width','Height'],true) ? 'min="0"' : '' ?> maxlength="<?= $key === 'ImageFileName' ? 50 : 255 ?>">
    <?php endif; ?></div>
    <?php endforeach; ?>
    <div class="field"><label for="image">Optional JPEG image upload (max 5 MB)</label>
    <input type="file" id="image" name="image" accept="image/jpeg,.jpg,.jpeg">
    <small>To use a Lab 14 image, enter its existing filename without .jpg. New uploads must use an unused filename.</small></div>
    <button type="submit" class="ui primary button"><?= $edit ? 'Save Changes' : 'Add Painting' ?></button>
    <a class="ui button" href="<?= $edit ? painting_url($id) : 'paintings.php' ?>">Cancel</a>
    </form>
<?php }
