<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register & Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="style.css" />

  <!-- ✅ Only include new Google Identity script -->
  <script src="https://accounts.google.com/gsi/client" async defer></script>

  <style>
    .g_id_signin {
      margin: 20px auto;
      display: flex;
      justify-content: center;
    }
  </style>
</head>
<body>

<div class="container" id="signUp" style="display:none;">
  <h1 class="form-title">Register</h1>
  <form method="post" action="register.php">
    <div class="input-group">
       <i class="fas fa-user"></i>
       <input type="text" name="fName" id="fName" placeholder="First Name" required>
       <label for="fname">First Name</label>
    </div>
    <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="lName" id="lName" placeholder="Last Name" required>
        <label for="lName">Last Name</label>
    </div>
    <div class="input-group">
        <i class="fas fa-envelope"></i>
        <input type="email" name="email" id="email" placeholder="Email" required>
        <label for="email">Email</label>
    </div>
    <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" id="password" placeholder="Password" required>
        <label for="password">Password</label>
    </div>
   <input type="submit" class="btn" value="Sign Up" name="signUp">
  </form>

  <div class="links">
  <p>Already have an account?</p>
  <button id="backToSignIn">Sign In</button>
</div>
</div>

<!-- SIGN IN FORM -->
<div class="container" id="signIn">
  <h1 class="form-title">Sign In</h1>
  <form method="post" action="register.php">
    <div class="input-group">
      <i class="fas fa-envelope"></i>
      <input type="email" name="email" id="signinemail" placeholder="Email" required />
      <label for="signinemail">Email</label>
    </div>
    <div class="input-group">
      <i class="fas fa-lock"></i>
      <input type="password" name="password" id="signinpassword" placeholder="Password" required />
      <label for="signinpassword">Password</label>
    </div>
    <input type="submit" class="btn" value="Sign In" name="signIn" />
  </form>

  <p class="or">----------or----------</p>

  <!-- ✅ New Google Sign-In button -->
  <div id="g_id_onload"
       data-client_id="702625291860-lpiojfrtpqm7aclgs6i3jk5jgam5qfrp.apps.googleusercontent.com"
       data-login_uri="http://localhost/login/homepage.php"
       data-callback="handleCredentialResponse"
       data-auto_prompt="false">
  </div>

  <div class="g_id_signin"
       data-type="standard"
       data-size="large"
       data-theme="outline"
       data-text="sign_in_with"
       data-shape="rectangular"
       data-logo_alignment="left">
  </div>

  <div class="links">
    <p>Don't have an account yet?</p>
    <button id="signUpButton">Sign Up</button>
  </div>
</div>


<script src="script.js"></script>


<script>
function handleCredentialResponse(response) {
  const responsePayload = parseJwt(response.credential);

  const name = responsePayload.name;
  const email = responsePayload.email;

  window.location.href = `google-login.php?name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}`;
}

function parseJwt(token) {
  const base64Url = token.split('.')[1];
  const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
  const jsonPayload = decodeURIComponent(
    atob(base64).split('').map(c => {
      return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join('')
  );

  return JSON.parse(jsonPayload);
}

document.addEventListener('DOMContentLoaded', () => {
  const signInForm = document.getElementById('signIn');
  const signUpForm = document.getElementById('signUp');
  const signUpButton = document.getElementById('signUpButton');
  const backToSignIn = document.getElementById('backToSignIn');

  signUpButton.addEventListener('click', () => {
    signInForm.style.display = 'none';
    signUpForm.style.display = 'block';
  });

  backToSignIn.addEventListener('click', () => {
    signUpForm.style.display = 'none';
    signInForm.style.display = 'block';
  });
});


</script>




</body>
</html>
