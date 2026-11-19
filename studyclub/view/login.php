<?php
session_start();
$errorMsg = $_SESSION['error'] ?? ($_SESSION['login_error'] ?? '');
unset($_SESSION['error'], $_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Study Club</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../public/CSS/style.css?v=9">
</head>

<body>
  <main class="card">
    <div class="brand">
      <img src="../public/img/logo.png" alt="Logo Study Club">
      <span>Study Club</span>
    </div>

    <?php if ($errorMsg !== ''): ?>
      <p class="auth-alert" role="alert"><?= htmlspecialchars($errorMsg) ?></p>
    <?php endif; ?>

    <form action="../control/prosesLogin.php" method="post">
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" autocomplete="email" required>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>
      </div>
      <button type="submit">Login</button>
    </form>

    <p class="switch">Tidak punya akun? <a href="../view/register.php">Register</a></p>
  </main>
</body>

</html>