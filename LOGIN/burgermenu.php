<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connect.php';

$profile_pic = "https://via.placeholder.com/34"; // default fallback

if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['profile_pic'])) {
            $profile_pic = "/LOGIN/" . $row['profile_pic']; // 👈 correct relative URL
        }
    }
}
?>

<style>
  .burger {
    position: fixed;
    top: 24px;
    left: 20px;
    width: 42px;
    height: 42px;
    cursor: pointer;  
    z-index: 1002;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgb(236, 253, 0);
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    transition: background-color 0.3s ease, transform 0.2s ease;
  }

  .burger:hover {
    background-color: rgb(236, 253, 0);
    transform: scale(1.05);
  }

  .burger-line {
    position: absolute;
    width: 22px;
    height: 3px;
    background-color: #fff;
    border-radius: 4px;
    transition: 0.3s ease;
  }

  .burger-line:nth-child(1) { top: 12px; }
  .burger-line:nth-child(2) { top: 20px; }
  .burger-line:nth-child(3) { top: 28px; }

  #menu-toggle:checked + .burger .burger-line:nth-child(1) {
    top: 20px;
    transform: rotate(45deg);
  }

  #menu-toggle:checked + .burger .burger-line:nth-child(2) {
    opacity: 0;
  }

  #menu-toggle:checked + .burger .burger-line:nth-child(3) {
    top: 20px;
    transform: rotate(-45deg);
  }

  .sidebar {
    position: fixed;
    top: 0;
    left: -220px;
    width: 220px;
    height: 100%;
    background-color:rgba(255, 255, 255, 0.86);
    padding-top: 100px;
    overflow-x: hidden;
    transition: left 0.3s ease;
    z-index: 1000;
  }

  .sidebar a {
    display: block;
    padding: 18px 20px;
    color:rgb(24, 23, 23);
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.3s ease;
    font-weight: 500;
  }

  .sidebar a:hover {
    background-color:rgb(236, 253, 0);
    padding-left: 30px;
    transform: scale(1.05);
  }

  #menu-toggle:checked ~ .sidebar {
    left: 0;
  }

  #backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
    display: none;
  }
</style>

<!-- Burger Menu HTML -->
<div id="backdrop"></div>
<input type="checkbox" id="menu-toggle" hidden>
<label for="menu-toggle" class="burger">
  <div class="burger-line"></div>
  <div class="burger-line"></div>
  <div class="burger-line"></div>
</label>

<nav class="sidebar">
  <a href="/LOGIN/homepage.php">Home</a>
  <a href="/LOGIN/Hoverbar/MyCart.php">My Cart</a>
  <a href="/LOGIN/Hoverbar/Saved.php">Saved</a>
  <a href="/LOGIN/logout.php">Logout</a>
<div class="dropdown">
  <a href="javascript:void(0);" onclick="toggleCategory()" style="display: flex; justify-content: space-between; align-items: center;">
    Categories <i id="categoryArrow" class="fas fa-chevron-down" style="font-size: 14px;"></i>
  </a>
  <div class="submenu" id="categoryMenu" style="display: none;">
    <a href="/LOGIN/Ecommerce/fashion.php" style="padding-left: 35px;">👗 Fashion</a>
    <a href="/LOGIN/Ecommerce/household.php" style="padding-left: 35px;">🧹 Household</a>
    <a href="/LOGIN/Ecommerce/furnitures.php" style="padding-left: 35px;">🪑 Furniture</a>
    <a href="/LOGIN/Ecommerce/gadgets.php" style="padding-left: 35px;">📱 Gadgets</a>
    <a href="/LOGIN/Ecommerce/others.php" style="padding-left: 35px;">❓ Others</a>
  </div>
</div>

</nav>

<!-- Profile Icon -->
<style>
  .profile-icon {
    position: fixed;
    top: 30px;
    right: 20px;
    width: 34px;
    height: 34px;
    background-color: #ecf0f1;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1001;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    transition: background 0.3s ease;
  }

  .profile-icon:hover {
    background-color: #bdc3c7;
  }

  .profile-icon i {
    font-size: 18px;
    color: #2c3e50;
  }

  .profile-icon-tooltip {
    position: absolute;
    top: -25px;
    right: 0;
    background: #2c3e50;
    color: #fff;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
    display: none;
    white-space: nowrap;
  }

  .profile-icon:hover .profile-icon-tooltip {
    display: block;
  }

  
</style>

<a href="/LOGIN/Hoverbar/Profile.php" class="profile-icon" title="Profile">
  <div class="profile-icon-tooltip">View Profile</div>
  <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
</a>

<!-- Font Awesome CDN (if not already included) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<script>
  function toggleCategory() {
    const menu = document.getElementById("categoryMenu");
    const arrow = document.getElementById("categoryArrow");
    const isVisible = menu.style.display === "block";
    menu.style.display = isVisible ? "none" : "block";
    arrow.classList.toggle("fa-chevron-down", isVisible);
    arrow.classList.toggle("fa-chevron-up", !isVisible);
  }
</script>
