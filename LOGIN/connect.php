<?php

$host="localhost";
$username="root";
$password="";
$database="login";
$conn=new mysqli($host,$username,$password,$database);
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
}

?>

