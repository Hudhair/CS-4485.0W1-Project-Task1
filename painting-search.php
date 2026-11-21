<?php
// return paintings whose titles start with what the user typed
require_once __DIR__ . '/painting-common.php';
header('Content-Type: application/json; charset=utf-8');

$prefix = trim((string)($_GET['q'] ?? ''));
if ($prefix === '') {
    echo json_encode([]);
    exit;
}
// limit what can be searched (special chars)
$prefix = substr($prefix, 0, 100);
$escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $prefix);

try {
    $db = painting_db();
    $stmt = $db->prepare(
        "SELECT PaintingID, Title FROM Paintings " .
        "WHERE Title LIKE ? ESCAPE '!' " .
        "ORDER BY Title ASC, PaintingID ASC LIMIT 10"
    );
    $stmt->execute([$escaped . '%']);
    $results = [];
    foreach ($stmt->fetchAll() as $row) {
        $results[] = ['id' => (int)$row['PaintingID'], 'title' => $row['Title']];
    }
    echo json_encode($results, JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Search unavailable']);
}
