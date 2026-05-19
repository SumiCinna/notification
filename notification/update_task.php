<?php
/**
 * update_task.php
 * Accepts GET or POST from email links or forms to update a task status.
 * If status becomes 'In Progress' or 'Done' this script records timestamps
 * and redirects the user back to the tracking page.
 */
require 'db.php';

// Accept either GET or POST for convenience (email links use GET)
$params = $_REQUEST;
$id = isset($params['task_id']) ? (int)$params['task_id'] : (isset($params['id']) ? (int)$params['id'] : 0);
$status = isset($params['status']) ? trim($params['status']) : '';

if (!$id || $status === '') {
    // Missing data - redirect back to status page with error
    header('Location: /notification/status.php?error=missing');
    exit;
}

// Normalize status values
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

    // Ensure started_at and completed_at columns exist (add if missing)
    $cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tasks'")->fetchAll(PDO::FETCH_COLUMN);
    $alter = [];
    if (!in_array('started_at', $cols)) {
        $alter[] = "ADD COLUMN started_at DATETIME NULL";
    }
    if (!in_array('completed_at', $cols)) {
        $alter[] = "ADD COLUMN completed_at DATETIME NULL";
    }
    if (!empty($alter)) {
        $pdo->exec('ALTER TABLE tasks '.implode(', ', $alter));
    }

    if ($status_norm === 'In Progress') {
        // Set status and started_at if not already set
        $stmt = $pdo->prepare("UPDATE tasks SET status = ?, updated_at = NOW(), started_at = COALESCE(started_at, NOW()) WHERE id = ?");
        $stmt->execute([$status_norm, $id]);
    } elseif ($status_norm === 'Done') {
        // Set status and completed_at
        $stmt = $pdo->prepare("UPDATE tasks SET status = ?, updated_at = NOW(), completed_at = NOW() WHERE id = ?");
        $stmt->execute([$status_norm, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE tasks SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status_norm, $id]);
    }

    // Redirect back to tracking page with anchor to the task
    header('Location: /notification/status.php?updated=1#task-' . $id);
    exit;
} catch (Exception $e) {
    // Show a simple error message if something goes wrong when clicked from email
    http_response_code(500);
    echo '<h1>Error updating task</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}
