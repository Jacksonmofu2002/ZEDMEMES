<?php
session_start();
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$db   = 'zedmemes';
$user = 'root';      // change if not using default
$pass = '';          // change if you have a password
$charset = 'utf8mb4';
$port = '3307';      // your custom port

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
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

// Helper: Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Helper: Get logged-in username
function get_username($pdo) {
    if (!is_logged_in()) return null;
    $stmt = $pdo->prepare('SELECT username FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();
    return $row ? $row['username'] : null;
}

// Handle AJAX actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'signup') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($username) < 3 || strlen($password) < 5 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        exit;
    }

    // Check if username or email exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username or email already exists.']);
        exit;
    }

    // Insert new user
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
    if ($stmt->execute([$username, $email, $hash])) {
        $_SESSION['user_id'] = $pdo->lastInsertId();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed.']);
    }
    exit;
}

if ($action === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, password FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'status') {
    if (is_logged_in()) {
        echo json_encode([
            'logged_in' => true,
            'username' => get_username($pdo)
        ]);
    } else {
        echo json_encode(['logged_in' => false]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
exit;
?>