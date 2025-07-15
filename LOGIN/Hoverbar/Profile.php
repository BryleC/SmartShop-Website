<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['link_wallet'])) {
        $wallet_type = $_POST['wallet_type'];
        $wallet_fname = $_POST['wallet_fname'];
        $wallet_lname = $_POST['wallet_lname'];
        $wallet_number = $_POST['wallet_number'];

        $stmt = $conn->prepare("UPDATE users SET wallet_type = ?, wallet_firstname = ?, wallet_lastname = ?, wallet_number = ? WHERE user_id = ?");
        $stmt->bind_param("ssssi", $wallet_type, $wallet_fname, $wallet_lname, $wallet_number, $user_id);
        $stmt->execute();
        exit; // AJAX response ends here
    }

    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $postcode = $_POST['postcode'];
    $country = $_POST['country'];


    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $target_dir = "../uploads/";
        $filename = basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . time() . "_" . $filename;

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        
        
        if (in_array($imageFileType, $allowed)) {
            move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file);
            $relative_path = "uploads/" . basename($target_file);
            $update = $conn->prepare("UPDATE users SET phone = ?, address = ?, postcode = ?, country = ?, profile_pic = ? WHERE user_id = ?");
            $update->bind_param("sssssi", $phone, $address, $postcode, $country, $relative_path, $user_id);
        } else {
            echo "<script>alert('Invalid image type.');</script>";
        }
    } else {
        $update = $conn->prepare("UPDATE users SET phone = ?, address = ?, postcode = ?, country = ? WHERE user_id = ?");
        $update->bind_param("ssssi", $phone, $address, $postcode, $country, $user_id);
    }

    $update->execute();
}

$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$walletDisplay = '';
if (!empty($user['wallet_type'])) {
$displayMap = [
  'grabpay' => 'GrabPay',
  'gcash' => 'GCash',
  'maya' => 'Maya',
  'paypal' => 'PayPal'
];
$walletName = $displayMap[strtolower($user['wallet_type'])] ?? ucfirst($user['wallet_type']);
$walletDisplay = "<span style='font-weight: bold; color: green;'>✔ {$walletName} Linked!</span>";
}

$walletDataJSON = json_encode([
    'wallet_type' => $user['wallet_type'] ?? '',
    'wallet_fname' => $user['wallet_firstname'] ?? '',
    'wallet_lname' => $user['wallet_lastname'] ?? '',
    'wallet_number' => $user['wallet_number'] ?? ''
]);



$profile_pic = !empty($user['profile_pic']) ? "../" . $user['profile_pic'] : "https://www.shutterstock.com/image-vector/avatar-gender-neutral-silhouette-vector-600nw-2470054311.jpg";

include '../burgermenu.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Open Sans', sans-serif;
      background: linear-gradient(to right, #f5d442, #162447);
      color: #333;
    }

    .container {
      display: flex;
      justify-content: center;
      padding: 50px 20px;
      min-height: 100vh;
    }

    .profile-card {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      max-width: 900px;
      width: 100%;
      display: flex;
      flex-wrap: wrap;
      overflow: hidden;
    }

    .left-side {
      background: #162447;
      color: #fff;
      flex: 1;
      min-width: 250px;
      padding: 30px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .profile-pic-container {
      position: relative;
      width: 160px;
      height: 160px;
      margin-bottom: 15px;
    }

    .profile-pic-container img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      border: 4px solid #f5d442;
      object-fit: cover;
    }

    .overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border-radius: 50%;
      background: rgba(245, 212, 66, 0.7);
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.3s;
      cursor: pointer;
    }

    .profile-pic-container:hover .overlay {
      opacity: 1;
    }

    .overlay label {
      color: #162447;
      font-weight: bold;
      cursor: pointer;
      text-align: center;
    }

    .overlay input[type="file"] {
      display: none;
    }

    .left-side h3 {
      margin: 10px 0 5px;
    }

    .left-side p {
      font-size: 14px;
      opacity: 0.9;
    }

    .right-side {
      flex: 2;
      min-width: 300px;
      padding: 40px;
    }

    .right-side h2 {
      color: #162447;
      margin-bottom: 30px;
    }

    .profile-detail {
      margin-bottom: 20px;
    }

    .profile-detail label {
      font-weight: bold;
      display: block;
      margin-bottom: 6px;
      color: #162447;
    }

    .profile-detail input {
      width: 100%;
      padding: 10px;
      border: 2px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      transition: border-color 0.3s;
    }

    .profile-detail input:focus {
      border-color: #f5d442;
      outline: none;
    }

    .readonly {
      background-color: #f0f0f0;
      cursor: not-allowed;
    }

    .button-row {
      margin-top: 30px;
    }

    .save-button {
      background-color: #f5d442;
      color: #162447;
      padding: 12px 30px;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
      margin-right: 10px;
      transition: background 0.3s;
    }

    .save-button:hover {
      background-color: #e6c932;
    }

    @media (max-width: 768px) {
      .profile-card {
        flex-direction: column;
      }

      .left-side, .right-side {
        padding: 30px;
      }

      .profile-pic-container {
        width: 130px;
        height: 130px;
      }
    }


    .ewallet-link-btn {
  margin-top: 20px;
  padding: 10px 20px;
  background-color: #f5d442;
  color: #162447;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
}

.ewallet-modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0; top: 0;
  width: 100%; height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  justify-content: center;
  align-items: center;
}

.ewallet-modal-content {
  background-color: white;
  padding: 30px;
  border-radius: 12px;
  width: 90%;
  max-width: 400px;
  position: relative;
  text-align: center;
}

.ewallet-modal-content h3 {
  margin-bottom: 20px;
  color: #162447;
}

.wallet-grid {
  display: flex;
  justify-content: center;
  gap: 15px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}

.wallet-grid label {
  cursor: pointer;
}

.wallet-grid input[type="radio"] {
  display: none;
}

.wallet-grid img {
  width: 60px;
  height: 60px;
  border: 2px solid transparent;
  border-radius: 8px;
  transition: transform 0.2s ease;
}

.wallet-grid input[type="radio"]:checked + img {
  border-color: #f5d442;
  transform: scale(1.05);
}

.wallet-input {
  width: 100%;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
  margin-bottom: 15px;
}

.save-wallet {
  background-color: #162447;
  color: #fff;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
}

.close {
  position: absolute;
  right: 15px;
  top: 10px;
  font-size: 24px;
  color: #999;
  cursor: pointer;
}

  </style>
</head>
<body>

<div class="container">
  <form method="POST" enctype="multipart/form-data" id="profileForm" class="profile-card">

    <div class="left-side">
      <div class="profile-pic-container">
        <img src="<?php echo $profile_pic; ?>" alt="Profile Picture" id="profilePreview">
        <div class="overlay">
          <label for="uploadInput">📷<br>Change
            <input type="file" id="uploadInput" name="profile_pic" accept="image/*" onchange="document.getElementById('profileForm').submit();">
          </label>
        </div>
      </div>
      <h3><?php echo htmlspecialchars(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')); ?></h3>
      <p><?php echo htmlspecialchars($user['email']); ?></p>
      <button type="button" class="ewallet-link-btn" onclick="openWalletModal()">Link your E-Wallet</button>
      <div id="walletDisplay" style="margin-top: 15px;"><?php echo $walletDisplay; ?></div>
      


    </div>

    

    <div class="right-side">
      <h2>Edit Profile</h2>

      <div class="profile-detail">
        <label>Name</label>
        <input type="text" class="readonly" value="<?php echo htmlspecialchars(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')); ?>" readonly>
      </div>

      <div class="profile-detail">
        <label>Mobile Number</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required pattern="\d+" class="editable-field" readonly>
      </div>

      <div class="profile-detail">
        <label>Address</label>
        <input type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" class="editable-field" readonly>
      </div>

      <div class="profile-detail">
        <label>Postcode</label>
        <input type="text" name="postcode" value="<?php echo htmlspecialchars($user['postcode'] ?? ''); ?>" required pattern="\d+" class="editable-field" readonly>
      </div>

      <div class="profile-detail">
        <label>Email</label>
        <input type="text" class="readonly" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
      </div>

      <div class="profile-detail">
        <label>Country</label>
        <input type="text" name="country" value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>" class="editable-field" readonly>
      </div>

      <div class="button-row">
        <button type="button" id="editBtn" class="save-button" onclick="enableEdit()">Edit Details</button>
        <button type="submit" id="saveBtn" class="save-button" style="display: none;">Save Changes</button>
      </div>
    </div>
  </form>

  
</div>


<?php if ($user['role'] !== 'seller'): ?>
  <form method="GET" action="/LOGIN/Sellers/seller-register.php" style="text-align: center; margin-top: 00px; margin-bottom: 30px;">
    <button type="submit" class="save-button">Become a Seller</button>
  </form>
<?php else: ?>
  <div style="text-align: center; margin-top: 00px; margin-bottom: 30px;">
    <a href="/LOGIN/Sellers/SellerDashboard.php" class="save-button">Go to Seller Centre</a>
  </div>
<?php endif; ?>



<div id="ewalletModal" class="ewallet-modal">
  <div class="ewallet-modal-content">
    <span class="close" onclick="closeWalletModal()">&times;</span>
    <h3>Select Your E-Wallet</h3>
    <div class="wallet-grid">
      <label>
        <input type="radio" name="wallet" value="GCash">
        <img src="../Images/E-wallet/gcash.png" alt="GCash">
      </label>
      <label>
        <input type="radio" name="wallet" value="Paypal">
        <img src="../Images/E-wallet/paypal.png" alt="PayPal">
      </label>
      <label>
        <input type="radio" name="wallet" value="Maya">
        <img src="../Images/E-wallet/maya.png" alt="Maya">
      </label>
      <label>
        <input type="radio" name="wallet" value="Grabpay">
        <img src="../Images/E-wallet/grabpay.png" alt="GrabPay">
      </label>
    </div>


    <input type="text" id="walletFname" placeholder="First Name on Wallet" class="wallet-input">
    <input type="text" id="walletLname" placeholder="Last Name on Wallet" class="wallet-input">
    <input type="text" id="walletNumber" placeholder="Enter wallet number or email" class="wallet-input">

<p style="color: #c0392b; font-size: 14px; margin-top: 10px;">
  ⚠️ Please make sure your name and number match exactly with your E-Wallet account details — even capitalization must be the same.
</p>

<button class="save-wallet" onclick="saveWallet()">Save</button>


  </div>
</div>





<script>
function enableEdit() {
  document.querySelectorAll('.editable-field').forEach(input => input.removeAttribute('readonly'));
  document.getElementById('editBtn').style.display = 'none';
  document.getElementById('saveBtn').style.display = 'inline-block';
}

  function closeWalletModal() {
    document.getElementById("ewalletModal").style.display = "none";
  }

  // Optional: Close modal when clicking outside the modal content
  window.onclick = function(event) {
    const modal = document.getElementById("ewalletModal");
    if (event.target === modal) {
      modal.style.display = "none";
    }
  }

function saveWallet() {
  const selectedLogo = document.querySelector('input[name="wallet"]:checked');
  const walletType = selectedLogo ? selectedLogo.value : '';
  const fname = document.getElementById('walletFname').value.trim();
  const lname = document.getElementById('walletLname').value.trim();
  const number = document.getElementById('walletNumber').value.trim();

  if (!walletType || !fname || !lname || !number) {
    alert('Please complete all wallet fields.');
    return;
  }

  fetch('profile.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `link_wallet=1&wallet_type=${walletType}&wallet_fname=${encodeURIComponent(fname)}&wallet_lname=${encodeURIComponent(lname)}&wallet_number=${encodeURIComponent(number)}`
  })
  .then(res => res.text())
  .then(() => {
const displayNames = {
  grabpay: 'GrabPay',
  gcash: 'GCash',
  maya: 'Maya',
  paypal: 'PayPal'
};

const formattedWalletName = displayNames[walletType.toLowerCase()] || walletType;

document.getElementById('walletDisplay').innerHTML = `<span style="font-weight: bold; color: green;">✔ ${formattedWalletName} Linked!</span>`;
document.querySelector('.ewallet-link-btn').textContent = 'Change Account Linked';

// Update savedWalletData object so next modal opens pre-filled
savedWalletData.wallet_type = walletType;
savedWalletData.wallet_fname = fname;
savedWalletData.wallet_lname = lname;
savedWalletData.wallet_number = number;
    closeWalletModal();
  });
  
}

  const savedWalletData = <?php echo $walletDataJSON; ?>;

  // Change button text if wallet is already linked
  if (savedWalletData.wallet_type) {
    document.querySelector('.ewallet-link-btn').textContent = 'Change Account Linked';
  }

  function openWalletModal() {
    document.getElementById("ewalletModal").style.display = "flex";

    // Pre-select wallet type radio
    if (savedWalletData.wallet_type) {
      const selectedRadio = document.querySelector(`input[name="wallet"][value="${savedWalletData.wallet_type}"]`);
      if (selectedRadio) {
        selectedRadio.checked = true;
      }
    }

    // Pre-fill name and number
    document.getElementById('walletFname').value = savedWalletData.wallet_fname || '';
    document.getElementById('walletLname').value = savedWalletData.wallet_lname || '';
    document.getElementById('walletNumber').value = savedWalletData.wallet_number || '';
  }

</script>

</body>
</html>
