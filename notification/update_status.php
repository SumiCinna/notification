<?php
header('Content-Type: application/json');
require 'db.php';

$data = $_POST;
if (empty($data['id']) || empty($data['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing id or status']);
    exit;
}

$id = (int)$data['id'];
$status = trim($data['status']);

// Normalize status
$map = [
    'in progress' => 'In Progress',
    'inprogress' => 'In Progress',
    'done' => 'Done',
    'assigned' => 'Assigned'
];
$key = strtolower(str_replace('_', ' ', $status));
$status_norm = isset($map[$key]) ? $map[$key] : $status;

try {
    $pdo = getPDO();

    // Ensure started_at/completed_at columns exist
    $cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tasks'")->fetchAll(PDO::FETCH_COLUMN);
    $alter = [];
    if (!in_array('started_at', $cols)) $alter[] = "ADD COLUMN started_at DATETIME NULL";
    if (!in_array('completed_at', $cols)) $alter[] = "ADD COLUMN completed_at DATETIME NULL";
    if (!empty($alter)) $pdo->exec('ALTER TABLE tasks '.implode(', ', $alter));

    if ($status_norm === 'In Progress') {
        $stmt = $pdo->prepare('UPDATE tasks SET status = ?, updated_at = NOW(), started_at = COALESCE(started_at, NOW()) WHERE id = ?');
        $stmt->execute([$status_norm, $id]);
    } elseif ($status_norm === 'Done') {
        $stmt = $pdo->prepare('UPDATE tasks SET status = ?, updated_at = NOW(), completed_at = NOW() WHERE id = ?');
        $stmt->execute([$status_norm, $id]);
    } else {
        $stmt = $pdo->prepare('UPDATE tasks SET status = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$status_norm, $id]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update status: ' . $e->getMessage()]);
}
