<?php
header('Content-Type: application/json');
require 'db.php';

try {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT id, sender_name, sender_email, recipient_name, recipient_email, title, description, DATE_FORMAT(deadline, '%Y-%m-%d') AS deadline, priority, status, token, created_at, 
        DATE_FORMAT(started_at, '%Y-%m-%d %H:%i:%s') AS started_at, 
        DATE_FORMAT(completed_at, '%Y-%m-%d %H:%i:%s') AS completed_at,
        TIMESTAMPDIFF(DAY, COALESCE(started_at, created_at), IFNULL(completed_at, NOW())) AS days_pending
        FROM tasks ORDER BY created_at DESC");
    $tasks = $stmt->fetchAll();
    echo json_encode(['success' => true, 'tasks' => $tasks]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch tasks: ' . $e->getMessage()]);
}
