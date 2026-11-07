<?php
require_once __DIR__ . '/painting-common.php';
$db = painting_db();
$id = painting_id();
if ($id === null) { http_response_code(400); exit('Select a valid painting from paintings.php.'); }
$record = painting_find($db, $id);
if (!$record) { http_response_code(404); exit('Painting not found.'); }
$error = '';
$genres = painting_count($db, 'PaintingGenres', $id);
$subjects = painting_count($db, 'PaintingSubjects', $id);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        painting_require_csrf();
        if ((string)($_POST['PaintingID'] ?? '') !== (string)$id) throw new RuntimeException('Painting ID mismatch.');
        // Check again at deletion time, not only when rendering the confirmation page.
        $db->beginTransaction();
        $genres = painting_count($db, 'PaintingGenres', $id);
        $subjects = painting_count($db, 'PaintingSubjects', $id);
        if ($genres || $subjects) throw new RuntimeException("Cannot delete painting #$id: $genres painting-genre and $subjects painting-subject links exist. Delete those links first.");
        $stmt = $db->prepare('DELETE FROM Paintings WHERE PaintingID = ?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() !== 1) throw new RuntimeException('Painting no longer exists.');
        $db->commit();
        $_SESSION['paintings_notice'] = 'Painting deleted successfully.';
        header('Location: paintings.php'); exit;
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        $error = painting_error($e);
    }
}
painting_header('Delete Painting');
?>
<div class="ui segment">
<h2 class="ui header">Delete Painting #<?= (int)$id ?></h2>
<p><strong>Title:</strong> <?= painting_h($record['Title']) ?></p>
<p><strong>Artist:</strong> <?= painting_h(trim(($record['FirstName'] ?? '') . ' ' . ($record['LastName'] ?? '')) ?: 'Not assigned') ?></p>
<?php if ($error): ?><div class="ui negative message"><?= painting_h($error) ?></div><?php endif; ?>
<?php if ($genres || $subjects): ?>
<div class="ui warning message">This painting cannot be deleted while it has <?= (int)$genres ?> painting-genre and <?= (int)$subjects ?> painting-subject relationship(s). Remove those records first.</div>
<a class="ui button" href="<?= painting_url($id) ?>">Back to Painting</a>
<?php else: ?>
<p>Are you sure you want to permanently delete this painting? This cannot be undone.</p>
<form class="ui form" method="post" action="painting-delete.php?id=<?= (int)$id ?>" onsubmit="return confirm('Permanently delete this painting?');">
<input type="hidden" name="csrf" value="<?= painting_h($_SESSION['paintings_csrf']) ?>">
<input type="hidden" name="PaintingID" value="<?= (int)$id ?>">
<button type="submit" class="ui red button">Confirm Delete</button>
<a class="ui button" href="<?= painting_url($id) ?>">Cancel</a>
</form>
<?php endif; ?>
</div>
<?php painting_footer(); ?>
