<?php
session_start();
include 'connect.php';


if (isset($_GET['email']) && isset($_GET['name'])) {
    $email = $_GET['email']; 
    $name = $_GET['name'];

    // Split full name into first and last names (best-effort)
    $names = explode(" ", $name, 2);
    $firstName = $names[0];
    $lastName = $names[1] ?? '';

    // Check if the user already exists
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        // User exists
        $row = $result->fetch_assoc();
$_SESSION['user_id'] = $row['user_id'];
        $_SESSION['email'] = $email;
    } else {
        // Insert new Google user (password is NULL)
        $insert = $conn->prepare("INSERT INTO users (firstName, lastName, email, password) VALUES (?, ?, ?, NULL)");
        $insert->bind_param("sss", $firstName, $lastName, $email);
        if ($insert->execute()) {
            $_SESSION['user_id'] = $insert->insert_id;
            $_SESSION['email'] = $email;
        } else {
            echo "Failed to create user: " . $conn->error;
            exit;
        }
    }

    // Redirect to homepage
    header("Location: homepage.php");
    exit;
} else {
    echo "Invalid access: Missing email or name from Google.";
}
