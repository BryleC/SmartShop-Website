<?php
session_start();
include 'connect.php';

$firstname = "";
$lastname = "";
$profile_pic = "https://via.placeholder.com/34"; // default

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT user_id, firstname, lastname, profile_pic FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['user_id'];
        $firstname = $row['firstname'];
        $lastname = $row['lastname'];
    if (!empty($row['profile_pic'])) {
        $profile_pic = "/LOGIN/" . $row['profile_pic'];
        }
    }
}
include 'burgermenu.php';

$featuredQuery = "
    SELECT p.product_id, p.product_name, p.price, p.image_path, p.category, SUM(oi.quantity) AS total_ordered
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE p.status = 'available'
    GROUP BY oi.product_id
    ORDER BY total_ordered DESC
    LIMIT 12
";


$featuredResult = $conn->query($featuredQuery);

?>




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
        <a href="homepage.php">
             <img src="Images/img/HOP.png" class="logo" alt ="">
        </a>
        <div>
            <ul id="navbar">
                <li><a href="#home">Home</a></li>
                <li><a href="#shop">Shop</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="/LOGIN/Hoverbar/MyCart.php"><i class="fa-solid fa-cart-shopping"></i></a></li>

            </ul>
        </div>
    </section>
    
    <section id="home" class="hero">
        <h2>Discover Amazing </h2>
        <h1>Products</h1>
        <p>Enjoy great deals and fast delivery on thousands of items!</p>
        <a href="#shop">
        <button>Shop Now</button>
        </a>
    </section>


<section id="shop" class="section-p1">
    <div class="fe-box">
        <img src="Images/Categories/fashion-and-apparel.jpg" alt="Fashion">
        <h6>Fashion & Apparel</h6>
        <a href="/LOGIN/Ecommerce/fashion.php"><button>Browse</button></a>
    </div>
    <div class="fe-box">
        <img src="Images/Categories/household-essentials.webp" alt="Household">
        <h6>Household Essentials</h6>
        <a href="/LOGIN/Ecommerce/household.php"><button>Browse</button></a>
    </div>
    <div class="fe-box">
        <img src="Images/Categories/furnitures-and-fixtures.jpeg" alt="Furniture">
        <h6>Furnitures & Fixtures</h6>
        <a href="/LOGIN/Ecommerce/furnitures.php"><button>Browse</button></a>
    </div>
    <div class="fe-box">
        <img src="Images/Categories/electronics-and-gadgets.jpg" alt="Electronics">
        <h6>Electronics & Gadgets</h6>
        <a href="/LOGIN/Ecommerce/gadgets.php"><button>Browse</button></a>
    </div>
    <div class="fe-box">
        <img src="Images/Categories/others.png" alt="Others">
       <h6>Others & Miscellaneous</h6>
        <a href="/LOGIN/Ecommerce/others.php"><button>Browse</button></a>
    </div>

</section>

<div id="banner" class="section-m1">
  <h4>ShopSmart</h4>
  <h2>Don't have an account yet?</h2>
  <a href="/LOGIN/index.php" class="normal">
    <button class="normal">Create Account</button>
  </a>
</div>

<div id="product1" class="section-p1">
  <br><br><br>
  <h2>Featured Products</h2>
  <p>Popular Items in the Market 🔥</p>
<div class="carousel-wrapper">
  <div class="featured-carousel">
    <?php 
      $featuredItems = [];
      while ($row = $featuredResult->fetch_assoc()) {
          $featuredItems[] = $row;
      }
      // Duplicate items to make scroll loop nicer
      $allItems = array_merge($featuredItems, $featuredItems);
      foreach ($allItems as $row): 
    ?>
      <div class="featured-card" id="product-<?= $row['product_id'] ?>">
        <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>">
        <div class="info">
          <h3><?= htmlspecialchars($row['product_name']) ?></h3>
          <p>₱<?= number_format($row['price'], 2) ?></p>
          <p style="font-size: 12px; color: gray;">Ordered: <?= $row['total_ordered'] ?>x</p>
        </div>
        <a href="/LOGIN/Ecommerce/<?= $row['category'] ?>.php#product-<?= $row['product_id'] ?>">
          <button class="view-button">View</button>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<section id="about" class="section-p1" style="min-height: 100vh; background: #fff8dc;">

  <div class="team-intro">
    <br><br><br><br><br><br>
    <h2>About Our Team</h2>
    <br><br>
    <p>We are a group of passionate developers from De La Salle Lipa who collaborated on this e-commerce project as a capstone to our learning. Our goal was to create a user-friendly, full-featured platform that demonstrates our skills in frontend and backend development.
    We combined design thinking, programming logic, and teamwork to bring this marketplace to life.</p>
  </div>

  <!-- 🔻 Simple Divider -->
  <div class="divider-with-text">
    <br><br>
    <hr>
    <span>Meet the Developers</span>
    <hr>
  </div>

  <div class="background-shapes">
    <div class="shape shape1"></div>
    <div class="shape shape2"></div>
    <div class="shape shape3"></div>
  </div>

  <div class="carousel" id="carousel"></div>

  <div class="swipe-hint">Swipe &gt;&gt;</div>


  <div class="modal" id="modal">
    <div class="modal-content">
      <span class="close" id="modal-close">&times;</span>
      <img id="modal-img" src="" alt="Developer photo" />
      <h2 id="modal-name"></h2>
      <p id="modal-bio" class="modal-bio"></p>
      <a id="linkedin-link" class="modal-link" target="_blank">View LinkedIn</a>
    </div>
  </div>

  <div class="image-popup" id="image-popup">
    <img id="popup-image" src="" alt="Full-size developer" />
  </div>

</section>

<section id="contact" class="section-p1" style="background-color: #f9f9f9;">
  <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
  <h2>Contact Us</h2>
  <p><strong>Email:</strong> support@shopsmart.com</p>
  <p><strong>Phone:</strong> +63 912 345 6789</p>
  <p><strong>Address:</strong> 4th Floor Unit 402, Atdrmam Building, J.P. Laurel Highway, Lipa City, 4217 Batangas</p>
  <p><strong>Hours:</strong> Monday – Friday | 8:00 AM – 5:00 PM</p>
  <p>Have any questions or feedback about our platform? We'd love to hear from you!</p>
  <a href="https://docs.google.com/forms/d/e/1FAIpQLSc6mPrqGcha4JRat5gvAKe2Mk9n1uLpVs41SMC3mQJprWKlyw/viewform?usp=dialog" target="_blank">
  <button class="normal">Send Us a Message</button>
  </a>
  </section>

    <script src = "script_homepage.js"></script>



<script>
  const isLoggedIn = <?php echo isset($_SESSION['email']) ? 'true' : 'false'; ?>;

  if (!isLoggedIn) {
    // List of selectors you want to protect
    const protectedLinks = document.querySelectorAll('a[href$="shop.php"], a[href$="fashion.php"], a[href$="household.php"], a[href$="gadgets.php"], a[href$="furnitures.php"], a[href$="mycart.php"]');

    protectedLinks.forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = 'index.php'; // redirect to login/signup
      });
    });

    // Optionally also disable buttons directly
    const buttons = document.querySelectorAll('button');
    buttons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        const parentLink = btn.closest('a');
        if (parentLink && parentLink.getAttribute('href') !== 'index.php') {
          e.preventDefault();
          window.location.href = 'index.php';
        }
      });
    });
  }

const sections = document.querySelectorAll("section");
const navLinks = document.querySelectorAll("#navbar a");

window.addEventListener("scroll", () => {
  let current = "";

  sections.forEach((section) => {
    const rect = section.getBoundingClientRect();
    if (rect.top <= 120 && rect.bottom >= 120) {
      current = section.getAttribute("id");
    }
  });

  navLinks.forEach((link) => {
    link.classList.remove("active");
    if (link.getAttribute("href") === "#" + current) {
      link.classList.add("active");
    }
  });
});


const carousel = document.getElementById("carousel");
const modal = document.getElementById("modal");
const modalImg = document.getElementById("modal-img");
const modalName = document.getElementById("modal-name");
const modalBio = document.getElementById("modal-bio");
const modalLink = document.getElementById("linkedin-link");
const closeBtn = document.getElementById("modal-close");
const imagePopup = document.getElementById("image-popup");
const popupImage = document.getElementById("popup-image");

const developers = [
  {
    name: "Bryle Caibigan",
    bio: "Builds features that require both UI and server logic.",
    img: "Images/AboutUs/3.png",
    linkedin: "https://www.linkedin.com/in/bryle-lester-caibigan-319b95352/"
  },
  {
    name: "Earl Garcia",
    bio: "Works closely with the UI/UX Designer and Backend Developer.",
    img: "Images/AboutUs/1.png",
    linkedin: "https://www.linkedin.com/in/earl-emmanuel-garcia-934480349/"
  },
  {
    name: "Charmaine Maducot",
    bio: "Creates wireframes, mockups, and prototypes.",
    img: "Images/AboutUs/4.png",
    linkedin:"https://www.linkedin.com/in/charmaine-maducot-443043363/"
  },
  {
    name: "Lucky Sayas",
    bio: "Ensures performance and reliability of the app before release.",
    img: "Images/AboutUs/2.png",
    linkedin: "#"
  },
  {
    name: "Victor Tolentino",
    bio: "Supports deployment and integration efforts.",
    img: "Images/AboutUs/5.png",
    linkedin: "https://www.linkedin.com/in/victor-miguel-tolentino-713653364/"
  },
  {
    name: "James Villamil",
    bio: "Ensures scalability and handles version control.",
    img: "Images/AboutUs/6.png",
    linkedin: "#"
  }
];

function shuffle(array) {
  for (let i = array.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [array[i], array[j]] = [array[j], array[i]];
  }
}

shuffle(developers);

developers.forEach(dev => {
  const card = document.createElement("div");
  card.className = "card";
  card.innerHTML = `
    <img src="${dev.img}" alt="${dev.name}" />
    <h3>${dev.name}</h3>
    <p>Click to learn more</p>
  `;

  card.querySelector("img").onclick = () => {
    modalImg.src = dev.img;
    modalName.textContent = dev.name;
    modalBio.textContent = dev.bio;
    modalLink.href = dev.linkedin;
    modal.style.display = "flex";
  };

  carousel.appendChild(card);
});

closeBtn.onclick = () => modal.style.display = "none";
window.onclick = (e) => {
  if (e.target === modal) modal.style.display = "none";
  if (e.target === imagePopup) imagePopup.style.display = "none";
};

modalImg.onclick = () => {
  popupImage.src = modalImg.src;
  imagePopup.style.display = "flex";
};
</script>



</body>

</html>