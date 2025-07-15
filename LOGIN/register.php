<?php 
session_start(); // ✅ always at the top

include 'connect.php';

if (isset($_POST['signUp'])) {
    $firstName = $_POST['fName'];
    $lastName  = $_POST['lName'];
    $email     = $_POST['email'];
    $password  = md5($_POST['password']); // You can upgrade to password_hash() later

    // Check if email already exists
    $checkEmail = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($checkEmail);

    if ($result->num_rows > 0) {
        echo "Email Address Already Exists!";
    } else {
        $insertQuery = "INSERT INTO users(firstName, lastName, email, password)
                        VALUES ('$firstName', '$lastName', '$email', '$password')";
        if ($conn->query($insertQuery) === TRUE) {
            header("Location: index.php"); // redirect to login page
            exit;
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

if (isset($_POST['signIn'])) {
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['email'] = $row['email'];
        $_SESSION['user_id'] = $row['user_id']; // ✅ fix: make sure it matches your table
        header("Location: homepage.php");
        exit;
    } else {
        echo "Not Found, Incorrect Email or Password";
    }
}
?>
