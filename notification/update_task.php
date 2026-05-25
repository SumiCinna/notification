<?php
/**
 * update_task.php
 * Accepts GET or POST from email links or forms to update a task status.
 * If status becomes 'In Progress' or 'Done' this script records timestamps
 * and redirects the user back to the tracking page.
 */
require __DIR__ . '/db.php';

// Accept either GET or POST for convenience (email links use GET)
$params = $_REQUEST;
$id = isset($params['task_id']) ? (int)$params['task_id'] : (isset($params['id']) ? (int)$params['id'] : 0);

$status = isset($params['status']) ? trim($params['status']) : '';


if (!empty($params['task_id']) && $status === '') {
    $status = 'In Progress';
}

if (!$id || $status === '') {
    header('Location: /notification/update_task.php');
    exit;
}

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

        if ($status_norm === 'In Progress' && (!isset($_GET['ajax']) || $_GET['ajax'] !== '1')) {
                $loaderUpdateUrl = '/notification/start_task.php?task_id=' . urlencode((string)$id) . '&status=In%20Progress&ajax=1';
                $loaderRedirectUrl = '/notification/start_task.php?task_id=' . $id;
                ?>
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8" />
                    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                    <title>Starting Task...</title>
                    <link rel="stylesheet" href="/notification/assets/css/style.css" />
                </head>
                    <body class="loader-body">
                    <div class="loader-card">
                        <div class="spinner" aria-hidden="true"></div>
                        <h1>Starting task...</h1>
                        <p>Please wait while the task status is updated to In Progress.</p>
                        <div class="task-id">Task #<?php echo htmlspecialchars((string)$id); ?></div>
                    </div>

                    <script>
                        (async function () {
                            try {
                                const response = await fetch('<?php echo $loaderUpdateUrl; ?>', { credentials: 'same-origin' });
                                const data = await response.json();
                                if (data && data.success) {
                                    window.location.replace('<?php echo $loaderRedirectUrl; ?>');
                                    return;
                                }
                                document.querySelector('.loader-card').innerHTML = '<h1>Unable to update task</h1><p>Please return to the status page and try again.</p>';
                            } catch (error) {
                                document.querySelector('.loader-card').innerHTML = '<h1>Network error</h1><p>' + (error && error.message ? error.message : 'Unable to reach the server.') + '</p>';
                            }
                        })();
                    </script>
                </body>
                </html>
                <?php
                exit;
        }

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

    if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'id' => $id, 'status' => $status_norm]);
        exit;
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
