<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// POST requests for tasks
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $task_id = $_POST['task_id'] ?? null;
    $task = $_POST['task'] ?? null;

    // Add task
    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, task) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $task);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "task_id" => $stmt->insert_id]);
        } else {
            echo json_encode(["success" => false, "error" => "Failed to add task"]);
        }
        exit;
    }

    // Edit task
    elseif ($action === 'edit') {
        $stmt = $conn->prepare("UPDATE tasks SET task = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $task, $task_id, $user_id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Failed to update task"]);
        }
        exit;
    }

    // Delete task
    elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Failed to delete task"]);
        }
        exit;
    }

    // Complete task
    elseif ($action === 'complete') {
        $stmt = $conn->prepare("UPDATE tasks SET is_completed = !is_completed WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Failed to update task status"]);
        }
        exit;
    }

    // Invalid action
    else {
        echo json_encode(["success" => false, "error" => "Invalid action"]);
        exit;
    }
}

// GET request to fetch tasks
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("SELECT id, task, is_completed FROM tasks WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
    echo json_encode(["success" => true, "tasks" => $tasks]);
    exit;
}

// Invalid request method
else {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
    exit;
}

$conn->close();  // Close db connection
?>