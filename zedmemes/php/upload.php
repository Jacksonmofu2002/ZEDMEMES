<?php
session_start();
header('Content-Type: application/json');

// Only allow logged in users
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to upload a meme.']);
    exit;
}

// Check method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

// Check file
if (!isset($_FILES['meme_image']) || $_FILES['meme_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Image upload failed.']);
    exit;
}

// Validate image type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$file_type = mime_content_type($_FILES['meme_image']['tmp_name']);
if (!in_array($file_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF, and WEBP images allowed.']);
    exit;
}

// Prepare upload directory
$upload_dir = '../assets/memes/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Unique filename
$ext = pathinfo($_FILES['meme_image']['name'], PATHINFO_EXTENSION);
$filename = uniqid('meme_', true) . '.' . $ext;
$filepath = $upload_dir . $filename;
$relative_path = 'assets/memes/' . $filename;

// Move uploaded file
if (!move_uploaded_file($_FILES['meme_image']['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file.']);
    exit;
}

// Store in database
$host = 'localhost';
$db   = 'zedmemes';
$user = 'root';      // Change if not default
$pass = '';          // Change if you have a password
$charset = 'utf8mb4';
$port = '3307';

$dsn = "mysql:host=$host;dbname=$db; port=$port;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO memes (user_id, image_path) VALUES (?, ?)');
if ($stmt->execute([$_SESSION['user_id'], $relative_path])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
exit;
?>