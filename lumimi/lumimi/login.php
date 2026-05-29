<?php
require_once __DIR__ . '/config.php';

use Lumiere\Auth\Session;
use Lumiere\Controllers\AuthController;

if (Session::isLoggedIn()) {
    header('Location: ' . (Session::isAdmin() ? 'admin.php' : 'index.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = AuthController::login($_POST['username'] ?? '', $_POST['senha'] ?? '');
    if ($result['success']) {
        header('Location: ' . (Session::isAdmin() ? 'admin.php' : 'index.php'));
        exit;
    }
    $error = $result['error'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lumière Decor — Acesso</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --primary:    #6D5C3A;
    --primary-lt: #E8D1A7;
    --primary-lg: #F7E0B5;
    --secondary:  #F7E7CE;
    --secondary-d:#B7A993;
    --bg:         #FFFEFD;
    --dark:       #141414;
    --dark-lt:    #2E2E2E;
    --light:      #FFFFFF;
    --success:    #52D65F;
    --border:     rgba(109,92,58,.15);
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Montserrat', sans-serif;
    background: var(--bg);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
  }
  body::before {
    content: '';
    position: fixed; inset: 0;
    background:
      radial-gradient(ellipse 70% 60% at 20% 80%, rgba(232,209,167,.35) 0%, transparent 60%),
      radial-gradient(ellipse 50% 50% at 80% 20%, rgba(247,224,181,.25) 0%, transparent 55%);
    pointer-events: none;
    z-index: 0;
  }
  .login-wrap {
    position: relative; z-index: 1;
    width: 100%; max-width: 420px;
    padding: 1rem;
  }
  .login-card {
    background: var(--light);
    border: 1px solid var(--border);
    border-radius: 2px;
    padding: 3rem 2.5rem;
    box-shadow: 0 20px 60px rgba(109,92,58,.12);
  }
  .brand {
    text-align: center;
    margin-bottom: 2.5rem;
  }
  .brand-icon {
    margin-bottom: 1rem;
  }
  .brand h1 {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    color: var(--primary);
    letter-spacing: .02em;
    margin-bottom: 0.2rem;
  }
  .brand span {
    font-size: .7rem;
    letter-spacing: .25em;
    text-transform: uppercase;
    color: var(--secondary-d);
    font-weight: 500;
  }
  label {
    font-size: .7rem;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--primary);
    font-weight: 600;
    margin-bottom: .4rem;
    display: block;
  }
  .form-control {
    border: 1px solid var(--border);
    border-radius: 1px;
    padding: .75rem 1rem;
    font-family: 'Montserrat', sans-serif;
    font-size: .9rem;
    background: var(--bg);
    color: var(--dark);
    transition: border-color .2s, box-shadow .2s;
  }
  .form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(109,92,58,.1);
    background: var(--light);
    outline: none;
  }
  .btn-lumiere {
    width: 100%;
    background: var(--primary);
    color: var(--light);
    border: none;
    border-radius: 1px;
    padding: .85rem;
    font-family: 'Montserrat', sans-serif;
    font-size: .75rem;
    letter-spacing: .18em;
    text-transform: uppercase;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s, transform .1s;
    margin-top: .5rem;
  }
  .btn-lumiere:hover { background: var(--dark-lt); }
  .btn-lumiere:active { transform: scale(.99); }
  .alert-lumiere {
    background: rgba(209,28,0,.08);
    border: 1px solid rgba(209,28,0,.25);
    border-radius: 1px;
    color: #d11c00;
    font-size: .82rem;
    padding: .7rem 1rem;
    margin-bottom: 1.2rem;
  }
  .hint {
    text-align: center;
    margin-top: 1.5rem;
    font-size: .75rem;
    color: var(--secondary-d);
  }
  .hint strong { color: var(--primary); }
  .divider {
    border: none;
    border-top: 1px solid var(--border);
    margin: 1.8rem 0;
  }
  html, body {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
  }
  .login-wrap {
    width: 100%;
    max-width: 100%;
    padding: clamp(.75rem, 4vw, 1rem);
  }
  .login-card {
    width: 100%;
    max-width: 420px;
    margin: 0 auto;
    padding: clamp(1.75rem, 6vw, 3rem) clamp(1.25rem, 5vw, 2.5rem);
  }
  .form-control { width: 100%; max-width: 100%; }
  @media (max-width: 576px) {
    body { align-items: flex-start; padding: 1rem 0; overflow-y: auto; }
    .brand h1 { font-size: 1.5rem; }
    .hint { font-size: .7rem; line-height: 1.6; }
  }
</style>
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="brand">
      <div class="brand-icon">
        <img src="logo.png" alt="Logo Lumière" style="width: 80px; height: auto; max-height: 80px; object-fit: contain;">
      </div>
      <h1>Lumière</h1>
      <span>Decor &amp; Locações</span>
    </div>

    <?php if ($error): ?>
    <div class="alert-lumiere"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label for="username">Usuário</label>
        <input type="text" class="form-control" id="username" name="username"
               placeholder="seu.usuario" autocomplete="username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label for="senha">Senha</label>
        <input type="password" class="form-control" id="senha" name="senha"
               placeholder="••••••••" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn-lumiere">
        <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
      </button>
    </form>

    <hr class="divider">
    <div class="hint">
      <strong>admin</strong> / password &nbsp;·&nbsp; <strong>usuario</strong> / password
      <br><br>
      Não tem conta? <a href="cadastro.php" style="color: var(--primary); text-decoration: none; font-weight: 600; border-bottom: 1px solid var(--primary);">Cadastre-se aqui</a>
    </div>
  </div>
</div>
</body>
</html>

```