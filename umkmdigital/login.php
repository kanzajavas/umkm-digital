<?php
session_start();
include 'koneksi.php';

$error = '';

if (isset($_POST['login'])) {

  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  // Validasi input
  if ($username === '' || $password === '') {

    $error = 'Username dan password wajib diisi.';

  } else {

    // Cari user berdasarkan username
    $stmt = mysqli_prepare(
      $koneksi,
      "SELECT * FROM tb_user WHERE username = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {

      $data = mysqli_fetch_assoc($result);

      /*
       * Password masih mengikuti database kamu.
       * Jadi belum menggunakan password_hash().
       */
      if ($password === $data['password']) {

        // Membuat session ID baru setelah login
        session_regenerate_id(true);

        // Session user
        $_SESSION['id'] = $data['id'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['role'] = $data['role'];

        // Redirect berdasarkan role
        if ($data['role'] === 'admin') {

          header('Location: dashboard/dashboard.php');
          exit;

        } elseif ($data['role'] === 'pelanggan') {

          header('Location: france.php');
          exit;

        } else {

          $error = 'Role pengguna tidak valid.';
        }

      } else {

        $error = 'Username atau password salah.';
      }

    } else {

      $error = 'Username atau password salah.';
    }

    mysqli_stmt_close($stmt);
  }
}
?>

<!doctype html>
<html lang="en" data-bs-theme="auto">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>Login - UMKM Digital</title>

  <script src="assets/js/color-modes.js"></script>

  <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    * {
      box-sizing: border-box;
    }

    body {
      min-height: 100vh;
      margin: 0;

      background:
        radial-gradient(circle at top left,
          rgba(13, 110, 253, 0.18),
          transparent 35%),
        radial-gradient(circle at bottom right,
          rgba(111, 66, 193, 0.18),
          transparent 35%),
        var(--bs-body-bg);

      font-family: Arial, Helvetica, sans-serif;
    }

    .login-wrapper {
      width: 100%;
      max-width: 430px;
      padding: 20px;
    }

    .login-card {
      background: var(--bs-body-bg);
      border: 1px solid var(--bs-border-color);
      border-radius: 24px;
      padding: 38px 35px;

      box-shadow:
        0 15px 45px rgba(0, 0, 0, 0.10);

      position: relative;
      overflow: hidden;
    }

    .login-card::before {
      content: "";

      position: absolute;

      width: 150px;
      height: 150px;

      background: rgba(13, 110, 253, 0.08);

      border-radius: 50%;

      top: -75px;
      right: -75px;
    }

    .login-card::after {
      content: "";

      position: absolute;

      width: 120px;
      height: 120px;

      background: rgba(111, 66, 193, 0.07);

      border-radius: 50%;

      bottom: -60px;
      left: -60px;
    }

    .login-content {
      position: relative;
      z-index: 2;
    }

    .logo-wrapper {
      width: 85px;
      height: 85px;

      margin: 0 auto 20px;

      border-radius: 20px;

      display: flex;
      align-items: center;
      justify-content: center;

      background: var(--bs-tertiary-bg);

      border: 1px solid var(--bs-border-color);

      box-shadow:
        0 8px 20px rgba(0, 0, 0, 0.08);
    }

    .logo-wrapper img {
      width: 65px;
      height: auto;
      object-fit: contain;
    }

    .login-title {
      font-size: 28px;
      font-weight: 700;

      text-align: center;

      margin-bottom: 7px;
    }

    .login-subtitle {
      text-align: center;

      color: var(--bs-secondary-color);

      font-size: 14px;

      margin-bottom: 30px;
    }

    .login-alert {
      border-radius: 12px;

      font-size: 14px;

      margin-bottom: 18px;
    }

    .form-floating {
      margin-bottom: 16px;
    }

    .form-control {
      height: 58px;

      border-radius: 13px;

      border: 1px solid var(--bs-border-color);

      padding-left: 16px;

      transition: all 0.2s ease;
    }

    .form-control:focus {
      border-color: #0d6efd;

      box-shadow:
        0 0 0 4px rgba(13, 110, 253, 0.12);
    }

    .form-floating label {
      color: var(--bs-secondary-color);
    }

    .login-button {
      width: 100%;
      height: 55px;

      border: none;

      border-radius: 13px;

      font-size: 16px;

      font-weight: 600;

      transition: all 0.25s ease;

      box-shadow:
        0 8px 18px rgba(13, 110, 253, 0.22);
    }

    .login-button:hover {
      transform: translateY(-2px);

      box-shadow:
        0 12px 24px rgba(13, 110, 253, 0.30);
    }

    .login-button:active {
      transform: translateY(0);
    }

    .register-text {
      text-align: center;

      margin-top: 23px;
      margin-bottom: 0;

      color: var(--bs-secondary-color);

      font-size: 14px;
    }

    .register-link {
      color: #0d6efd;

      text-decoration: none;

      font-weight: 600;
    }

    .register-link:hover {
      text-decoration: underline;
    }

    .copyright {
      text-align: center;

      margin-top: 28px;
      margin-bottom: 0;

      color: var(--bs-secondary-color);

      font-size: 12px;
    }

    .bd-mode-toggle {
      z-index: 1500;
    }

    .bd-mode-toggle .bi {
      width: 1em;
      height: 1em;
    }

    @media (max-width: 480px) {

      .login-wrapper {
        padding: 15px;
      }

      .login-card {
        padding: 30px 23px;

        border-radius: 20px;
      }

      .login-title {
        font-size: 25px;
      }

      .logo-wrapper {
        width: 75px;
        height: 75px;
      }

      .logo-wrapper img {
        width: 57px;
      }
    }
  </style>
</head>

<body class="d-flex align-items-center justify-content-center">

  <!-- Theme Toggle -->
  <div class="dropdown position-fixed bottom-0 end-0 mb-3 me-3 bd-mode-toggle">

    <button class="btn btn-primary py-2 px-3 dropdown-toggle d-flex align-items-center" id="bd-theme" type="button"
      aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme">

      <svg class="bi my-1 theme-icon-active" width="16" height="16" aria-hidden="true">

        <use href="#circle-half"></use>

      </svg>

      <span class="visually-hidden" id="bd-theme-text">

        Toggle theme

      </span>

    </button>

    <ul class="dropdown-menu dropdown-menu-end shadow">

      <li>

        <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light">

          <svg class="bi me-2 opacity-50" width="16" height="16">

            <use href="#sun-fill"></use>

          </svg>

          Light

          <svg class="bi ms-auto d-none" width="16" height="16">

            <use href="#check2"></use>

          </svg>

        </button>

      </li>

      <li>

        <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark">

          <svg class="bi me-2 opacity-50" width="16" height="16">

            <use href="#moon-stars-fill"></use>

          </svg>

          Dark

          <svg class="bi ms-auto d-none" width="16" height="16">

            <use href="#check2"></use>

          </svg>

        </button>

      </li>

      <li>

        <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto">

          <svg class="bi me-2 opacity-50" width="16" height="16">

            <use href="#circle-half"></use>

          </svg>

          Auto

          <svg class="bi ms-auto d-none" width="16" height="16">

            <use href="#check2"></use>

          </svg>

        </button>

      </li>

    </ul>

  </div>


  <!-- SVG Icons -->

  <svg xmlns="http://www.w3.org/2000/svg" class="d-none">

    <symbol id="check2" viewBox="0 0 16 16">

      <path
        d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z">
      </path>

    </symbol>


    <symbol id="circle-half" viewBox="0 0 16 16">

      <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z">
      </path>

    </symbol>


    <symbol id="moon-stars-fill" viewBox="0 0 16 16">

      <path
        d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277 4.021 0 7.277-3.278 7.277-7.318 0-.527-.055-1.04-.16-1.533a.787.787 0 0 1 .316-.81.733.733 0 0 1 .893.031A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z">
      </path>

      <path
        d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a.217.217 0 0 0 0-.412l-1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162z">
      </path>

    </symbol>


    <symbol id="sun-fill" viewBox="0 0 16 16">

      <path
        d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1 .5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8z">
      </path>

    </symbol>

  </svg>


  <!-- LOGIN -->

  <main class="login-wrapper">

    <div class="login-card">

      <div class="login-content">

        <form method="POST">

          <!-- Logo -->

          <div class="logo-wrapper">

            <img src="assets/brand/umkmlogoo.png" alt="Logo UMKM">

          </div>


          <!-- Title -->

          <h1 class="login-title">
            Selamat Datang
          </h1>

          <p class="login-subtitle">
            Silakan login untuk melanjutkan ke akun Anda
          </p>


          <!-- Error -->

          <?php if ($error !== ''): ?>

            <div class="alert alert-danger login-alert text-center" role="alert">

              <?= htmlspecialchars($error); ?>

            </div>

          <?php endif; ?>


          <!-- Username -->

          <div class="form-floating">

            <input type="text" name="username" class="form-control" id="floatingInput" placeholder="Username"
              autocomplete="username" required>

            <label for="floatingInput">
              Username
            </label>

          </div>


          <!-- Password -->

          <div class="form-floating">

            <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password"
              autocomplete="current-password" required>

            <label for="floatingPassword">
              Password
            </label>

          </div>


          <!-- Login Button -->

          <button class="btn btn-primary login-button" type="submit" name="login">

            Login

          </button>


          <!-- Register -->

          <p class="register-text">

            Belum punya akun?

            <a href="register.php" class="register-link">

              Daftar sekarang

            </a>

          </p>


          <!-- Copyright -->

          <p class="copyright">
            &copy; 2026 UMKM Digital
          </p>

        </form>

      </div>

    </div>

  </main>


  <script src="assets/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>