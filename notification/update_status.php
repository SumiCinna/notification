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

$startedAt = isset($data['started_at']) ? normalizeDateTimeInput($data['started_at']) : null;
$completedAt = isset($data['completed_at']) ? normalizeDateTimeInput($data['completed_at']) : null;

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

    $fields = ['status = ?', 'updated_at = NOW()'];
    $values = [$status_norm];

    if ($startedAt !== null) {
        $fields[] = 'started_at = ?';
        $values[] = $startedAt;
    } elseif ($status_norm === 'In Progress' || $status_norm === 'Done') {
        $fields[] = 'started_at = COALESCE(started_at, NOW())';
    }

    if ($completedAt !== null) {
        $fields[] = 'completed_at = ?';
        $values[] = $completedAt;
    } elseif ($status_norm === 'Done') {
        $fields[] = 'completed_at = NOW()';
    }

    $values[] = $id;
    $stmt = $pdo->prepare('UPDATE tasks SET ' . implode(', ', $fields) . ' WHERE id = ?');
    $stmt->execute($values);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update status: ' . $e->getMessage()]);
}

function normalizeDateTimeInput($value)
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }

    $formats = ['Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i:s', 'Y-m-d H:i'];
    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $value);
        if ($date instanceof DateTime) {
            return $date->format('Y-m-d H:i:s');
        }
    }

    $timestamp = strtotime($value);
    if ($timestamp !== false) {
        return date('Y-m-d H:i:s', $timestamp);
    }

    return null;
}
