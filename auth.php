<?php
session_start();
include 'config.php'; //Connect to config file (database)

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'login':
            handleLogin();
            break;
        case 'register':
            handleRegister();
            break;
        case 'logout':
            handleLogout();
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

// User login function
function handleLogin() {
    global $conn; // in config.php file
    
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // SQL injection protection
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                // Check if login info is true
                $user = $result->fetch_assoc();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing username or password']);
    }
}

// Registration Function
function handleRegister() {
    global $conn;

    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Check if username exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo json_encode(['success' => false, 'error' => 'Username already exists']);
        } else {
            // Add new user into database
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $password);

            if ($stmt->execute()) {
                // Succesful registration
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $username;
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Database error']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing username or password']);
    }
}

// Logout Function
function handleLogout() {
    // Drop sessuin variables
    $_SESSION = array();

    // End the session
    session_destroy();

    echo json_encode(['success' => true]);
}

$conn->close(); // Close db connnetion
?>