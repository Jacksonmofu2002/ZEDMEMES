<?php
session_start();
header('Content-Type: application/json');

// Only allow logged in users
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required to react.']);
    exit;
}

// Validate POST data
$meme_id = isset($_POST['meme_id']) ? intval($_POST['meme_id']) : 0;
$reaction_type = isset($_POST['reaction_type']) ? $_POST['reaction_type'] : '';

if (!$meme_id || !in_array($reaction_type, ['like', 'upvote'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid reaction.']);
    exit;
}

// Database connection
$host = 'localhost';
$db   = 'zedmemes';
$user = 'root';      // Change if not default
$pass = '';          // Change if you have a password
$charset = 'utf8mb4';
$port = '3307';

$dsn = "mysql:host=$host;dbname=$db;port=$port;charset=$charset";
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

// Optional: Prevent duplicate reactions per user per meme per type
$stmt = $pdo->prepare('SELECT id FROM reactions WHERE meme_id = ? AND user_id = ? AND reaction_type = ?');
$stmt->execute([$meme_id, $_SESSION['user_id'], $reaction_type]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You have already reacted to this meme.']);
    exit;
}

// Insert reaction
$stmt = $pdo->prepare('INSERT INTO reactions (meme_id, user_id, reaction_type) VALUES (?, ?, ?)');
if ($stmt->execute([$meme_id, $_SESSION['user_id'], $reaction_type])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Could not record reaction.']);
}
exit;
?>