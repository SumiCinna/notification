<?php
header('Content-Type: application/json');
require 'db.php';

// Accept POST form data
$data = $_POST;
$required = ['sender_name','sender_email','task_title','task_description','task_deadline','task_priority'];
foreach ($required as $f) {
    if (empty($data[$f])) {
        http_response_code(400);
        echo json_encode(['error' => "Field $f is required"]);
        exit;
    }
}

$sender_name = trim($data['sender_name']);
$sender_email = filter_var($data['sender_email'], FILTER_VALIDATE_EMAIL);
$title = trim($data['task_title']);
$description = trim($data['task_description']);
$deadline = $data['task_deadline'];
$priority = trim($data['task_priority']);
$recipient_names = isset($data['recipient_name']) ? (array)$data['recipient_name'] : [];
$recipient_emails = isset($data['recipient_email']) ? (array)$data['recipient_email'] : [];

if (!$sender_email) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid sender email address provided']);
    exit;
}

if (count($recipient_names) === 0 || count($recipient_emails) === 0 || count($recipient_names) !== count($recipient_emails)) {
    http_response_code(400);
    echo json_encode(['error' => 'Recipient names and emails are required']);
    exit;
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("INSERT INTO tasks (sender_name,sender_email,recipient_name,recipient_email, title, description, deadline, priority, status, token) VALUES (?,?,?,?,?,?,?,?,?,?)");

    $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'];
    $headers = "From: $sender_email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $createdTasks = [];
    for ($i = 0; $i < count($recipient_names); $i++) {
        $recipient_name = trim($recipient_names[$i]);
        $recipient_email = filter_var(trim($recipient_emails[$i]), FILTER_VALIDATE_EMAIL);

        if ($recipient_name === '' || !$recipient_email) {
            continue;
        }

        $token = bin2hex(random_bytes(16));
        $stmt->execute([$sender_name, $sender_email, $recipient_name, $recipient_email, $title, $description, $deadline, $priority, 'Assigned', $token]);
        $id = $pdo->lastInsertId();

        // Send email via PHP mail() as backup
        $to = $recipient_email;
        $subject = "Task Assignment: $title";
        $message = "Hello $recipient_name,\n\n";
        $message .= "$sender_name has assigned you a new task.\n\n";
        $message .= "Task Title: $title\n";
        $message .= "Description: $description\n";
        $message .= "Deadline: $deadline\n";
        $message .= "Priority: $priority\n\n";
        $message .= "View and update status: $baseUrl/notification/status.php\n\n";
        $message .= "Task Tracking Link: $baseUrl/notification/status.php?token=$token";

        @mail($to, $subject, $message, $headers);

        $createdTasks[] = [
            'id' => $id,
            'token' => $token,
            'recipient_name' => $recipient_name,
            'recipient_email' => $recipient_email
        ];
    }

    if (count($createdTasks) === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Please provide at least one valid recipient']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'count' => count($createdTasks),
        'tasks' => $createdTasks,
        'id' => $createdTasks[0]['id'],
        'token' => $createdTasks[0]['token']
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save task: ' . $e->getMessage()]);
}
