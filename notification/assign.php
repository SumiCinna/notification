<?php
header('Content-Type: application/json');
require 'db.php';

// Accept POST form data
$data = $_POST;
$required = ['sender_name','sender_email','recipient_name','recipient_email','task_title','task_description','task_deadline','task_priority'];
foreach ($required as $f) {
    if (empty($data[$f])) {
        http_response_code(400);
        echo json_encode(['error' => "Field $f is required"]);
        exit;
    }
}

$sender_name = trim($data['sender_name']);
$sender_email = filter_var($data['sender_email'], FILTER_VALIDATE_EMAIL);
$recipient_name = trim($data['recipient_name']);
$recipient_email = filter_var($data['recipient_email'], FILTER_VALIDATE_EMAIL);
$title = trim($data['task_title']);
$description = trim($data['task_description']);
$deadline = $data['task_deadline'];
$priority = trim($data['task_priority']);

if (!$sender_email || !$recipient_email) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address provided']);
    exit;
}

try {
    $pdo = getPDO();
    $token = bin2hex(random_bytes(16));
    $stmt = $pdo->prepare("INSERT INTO tasks (sender_name,sender_email,recipient_name,recipient_email, title, description, deadline, priority, status, token) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$sender_name,$sender_email,$recipient_name,$recipient_email,$title,$description,$deadline,$priority,'Assigned',$token]);
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
    $message .= "View and update status: " . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . "/notification/status.php\n\n";
    $message .= "Task Tracking Link: " . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . "/notification/status.php?token=$token";
    
    $headers = "From: $sender_email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Attempt to send via PHP mail
    @mail($to, $subject, $message, $headers);

    echo json_encode(['success' => true, 'id' => $id, 'token' => $token]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save task: ' . $e->getMessage()]);
}
