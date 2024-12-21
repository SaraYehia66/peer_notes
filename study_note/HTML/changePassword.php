<?php
session_start();
include 'config.php'; // Include database connection

header('Content-Type: application/json'); // Set response type to JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($_SESSION['email'])) {
        echo json_encode(['message' => 'User not logged in.']);
        exit;
    }

    $user_email = $_SESSION['email'];
    $oldPassword = $input['oldPassword'];
    $newPassword = $input['newPassword'];

    $stmt = $connection->prepare("SELECT pass FROM signup WHERE email = ?");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($oldPassword, $row['pass'])) {
            $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $updateStmt = $connection->prepare("UPDATE signup SET pass = ? WHERE email = ?");
            $updateStmt->bind_param("ss", $newHashedPassword, $user_email);

            if ($updateStmt->execute()) {
                echo json_encode(['message' => 'Password updated successfully.']);
            } else {
                echo json_encode(['message' => 'Could not update password.']);
            }
        } else {
            echo json_encode(['message' => 'Old password is incorrect.']);
        }
    } else {
        echo json_encode(['message' => 'User account not found.']);
    }
}
?>
