<?php
require_once __DIR__ . '/painting-common.php';
$db = painting_db();
$id = painting_id();
if ($id === null) { http_response_code(400); exit('Select a valid painting from paintings.php.'); }
$record = painting_find($db, $id);
if (!$record) { http_response_code(404); exit('Painting not found.'); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        painting_require_csrf();
        if ((string)($_POST['PaintingID'] ?? '') !== (string)$id) throw new RuntimeException('Painting ID mismatch.');
        painting_save($db, $_POST, false);
        $_SESSION['paintings_notice'] = 'Painting updated successfully.';
        header('Location: ' . painting_url($id)); exit;
    } catch (Throwable $e) { $error = painting_error($e); }
}
painting_header('Edit Painting');
?>
<?php if ($error): ?><div class="ui negative message"><?= painting_h($error) ?></div><?php endif; ?>
<div class="ui segment"><h2 class="ui header">Edit <?= painting_h($record['Title']) ?> (#<?= (int)$id ?>)</h2>
<?php painting_form($db, $record, true, $id, $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : []); ?>
</div>
<?php painting_footer(); ?>
