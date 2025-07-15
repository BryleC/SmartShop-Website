<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Seller Registration</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #fff8dc, #f7f7f7);
      padding: 40px 20px;
      color: #162447;
    }

    .form-box {
      max-width: 600px;
      margin: auto;
      background: #fff;
      border-radius: 16px;
      padding: 40px 30px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      font-size: 28px;
      font-weight: 600;
      color: #162447;
    }

    label {
      font-weight: 500;
      display: block;
      margin-top: 18px;
      margin-bottom: 6px;
    }

    input, textarea {
      width: 100%;
      padding: 12px 14px;
      font-size: 14px;
      border-radius: 10px;
      border: 1px solid #ccc;
      transition: border 0.3s;
    }

    input:focus, textarea:focus {
      border-color: #f5d442;
      outline: none;
    }

    button {
      width: 100%;
      padding: 14px;
      background-color: #f5d442;
      color: #162447;
      border: none;
      border-radius: 12px;
      font-weight: bold;
      font-size: 16px;
      margin-top: 30px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    button:hover {
      background-color: #e6c020;
    }

    .links {
      text-align: center;
      margin-top: 25px;
    }

    .links a {
      display: inline-block;
      margin: 10px;
      color: #162447;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s ease;
    }

    .links a:hover {
      color: #f5d442;
    }

    @media (max-width: 600px) {
      .form-box {
        padding: 30px 20px;
      }
    }
  </style>
</head>
<body>

<div class="form-box">
  <h2>Become a Seller</h2>
  <form method="POST" action="process-seller-registration.php">
    <label for="shop_name">Shop Name</label>
    <input type="text" name="shop_name" id="shop_name" required>

    <label for="category">Business Category (Fashion, Household, Furniture, Gadgets)</label>
    <input type="text" name="category" id="category" required>

    <label for="shop_address">Shop Address</label>
    <textarea name="shop_address" id="shop_address" rows="3" required></textarea>

    <label for="shop_phone">Phone Number</label>
    <input type="text" name="shop_phone" id="shop_phone" required>

    <button type="submit">Register as Seller</button>

    <div class="links">
      <a href="SellerDashboard.php">Go to Seller Dashboard</a>
      <a href="/LOGIN/Hoverbar/Profile.php">Back to Profile</a>
    </div>
  </form>
</div>

</body>
</html>
