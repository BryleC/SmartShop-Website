<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style_homepage.css">
  <script src="https://kit.fontawesome.com/7f603afd0a.js" crossorigin="anonymous"></script>
</head>
<body>
  <section id="header">
    <a href="#"><img src="Images/img/HOP.png" class="logo" alt=""></a>
    <div>
      <ul id="navbar">
        <li><a class="active" href="index.php">Home</a></li>
        <li><a href="index.php">Shop</a></li>
        <li><a href="index.php">Blog</a></li>
        <li><a href="index.php">About</a></li>
        <li><a href="index.php">Contact</a></li>
        <li><a href="index.php"><i class="fa-solid fa-cart-shopping"></i></a></li>
      </ul>
    </div>
  </section>

  <section id="hero">
    <h2>Discover Amazing</h2>
    <h1>Products</h1>
    <p>Enjoy great deals and fast delivery on thousands of items!</p>
    <a href="index.php"><button>Shop Now</button></a>
  </section>

  <section id="feature" class="section-p1">
    <div class="fe-box">
      <img src="Images/Categories/fashion-and-apparel.jpg" alt="Fashion">
      <h6>Fashion & Apparel</h6>
      <a href="index.php"><button>Browse</button></a>
    </div>
    <div class="fe-box">
      <img src="Images/Categories/household-essentials.webp" alt="Household">
      <h6>Household Essentials</h6>
      <a href="index.php"><button>Browse</button></a>
    </div>
    <div class="fe-box">
      <img src="Images/Categories/furnitures-and-fixtures.jpeg" alt="Furniture">
      <h6>Furnitures & Fixtures</h6>
      <a href="index.php"><button>Browse</button></a>
    </div>
    <div class="fe-box">
      <img src="Images/Categories/electronics-and-gadgets.jpg" alt="Electronics">
      <h6>Electronics & Gadgets</h6>
      <a href="index.php"><button>Browse</button></a>
    </div>
  </section>

  <section id="banner" class="section-m1">
    <h4>ShopSmart</h4>
    <h2>Don't have an account yet?</h2>
    <a href="index.php"><button class="normal">Create Account</button></a>
  </section>

  <section id="product1" class="section-p1">
    <h2>Featured Products</h2>
    <p>Popular Items in the Market</p>
  </section>

  <script src="script_homepage.js"></script>

  <!-- JS Redirect for any click (extra safety) -->
  <script>
    document.querySelectorAll('a, button').forEach(el => {
      el.addEventListener('click', function(e) {
        const tag = e.target.closest('a') || e.target;
        if (tag && tag.tagName.toLowerCase() !== 'a') {
          e.preventDefault();
          window.location.href = 'index.php';
        }
      });
    });
  </script>
</body>
</html>
