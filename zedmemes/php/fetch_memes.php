<?php
session_start();
header('Content-Type: application/json');

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
    echo json_encode([]);
    exit;
}

// Fetch memes with uploader username, order: newest first
$sql = "
    SELECT m.id, m.image_path, m.uploaded_at, u.username
    FROM memes m
    JOIN users u ON m.user_id = u.id
    ORDER BY m.uploaded_at DESC
";
$stmt = $pdo->query($sql);
$memes = $stmt->fetchAll();

// Get reactions
foreach ($memes as &$meme) {
    $meme_id = $meme['id'];

    // Likes
    $stmt_likes = $pdo->prepare("SELECT COUNT(*) FROM reactions WHERE meme_id = ? AND reaction_type = 'like'");
    $stmt_likes->execute([$meme_id]);
    $meme['likes'] = $stmt_likes->fetchColumn();

    // Upvotes
    $stmt_upvotes = $pdo->prepare("SELECT COUNT(*) FROM reactions WHERE meme_id = ? AND reaction_type = 'upvote'");
    $stmt_upvotes->execute([$meme_id]);
    $meme['upvotes'] = $stmt_upvotes->fetchColumn();
}

echo json_encode($memes);
exit;
?>