<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar - Study Club</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../public/CSS/style.css">
</head>
<body>
  <main class="card">
    <div class="brand">
      <img src="../public/img/logo.png" alt="Logo Study Club">
      <span>Study Club</span>
    </div>

    <form action="../control/prosesRegis.php" method="post">
      <div class="field">
        <label for="nama">Nama</label>
        <input type="text" id="nama" name="nama" autocomplete="name" required>
      </div>
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" autocomplete="email" required>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" autocomplete="new-password" required>
      </div>
      <button type="submit">Daftar</button>
    </form>

    <p class="switch">Sudah punya akun? <a href="../view/login.php">Login</a></p>
  </main>
</body>
</html>
