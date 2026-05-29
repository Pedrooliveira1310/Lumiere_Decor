<?php
require_once __DIR__ . '/config.php';

use Lumiere\Auth\Session;
use Lumiere\Controllers\AuthController;

if (Session::isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $senha_confirm = $_POST['senha_confirm'] ?? '';

    if (!$username || !$senha) {
        $error = 'Usuário e senha são obrigatórios.';
    } elseif ($senha !== $senha_confirm) {
        $error = 'As senhas não coincidem.';
    } elseif (strlen($senha) < 6) {
        $error = 'A senha deve ter no mínimo 6 caracteres.';
    } else {
        $result = AuthController::register($username, $senha);
        if ($result['success']) {
            $success = 'Cadastro realizado com sucesso! Redirecionando para login...';
            header('refresh:2;url=login.php');
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lumière Decor — Cadastro</title>
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
    width: 60px; height: 60px;
    background: var(--primary-lt);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem; color: var(--primary);
  }
  .brand h1 {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    color: var(--primary);
    letter-spacing: .02em;
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
  .btn-lumiere-outline {
    width: 100%;
    background: var(--light);
    color: var(--primary);
    border: 1px solid var(--primary);
    border-radius: 1px;
    padding: .85rem;
    font-family: 'Montserrat', sans-serif;
    font-size: .75rem;
    letter-spacing: .18em;
    text-transform: uppercase;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s, color .2s, transform .1s;
    margin-top: .5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
  }
  .btn-lumiere-outline:hover { background: var(--primary-lt); color: var(--dark); }
  .btn-lumiere:active, .btn-lumiere-outline:active { transform: scale(.99); }
  .alert-lumiere {
    background: rgba(209,28,0,.08);
    border: 1px solid rgba(209,28,0,.25);
    border-radius: 1px;
    color: #d11c00;
    font-size: .82rem;
    padding: .7rem 1rem;
    margin-bottom: 1.2rem;
  }
  .alert-success-lumiere {
    background: rgba(82,214,95,.08);
    border: 1px solid rgba(82,214,95,.25);
    border-radius: 1px;
    color: #27A533;
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
  .hint a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    border-bottom: 1px solid var(--primary);
  }
  .hint a:hover { color: var(--dark-lt); }
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
        <img src="uploads/logo.png" alt="Logo Lumière" style="width: 100px; height: auto; max-height: 100px; object-fit: contain;">
      </div>
      <h1>Lumière</h1>
      <span>Decor &amp; Locações — Cadastro</span>
    </div>

    <?php if ($error): ?>
    <div class="alert-lumiere"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert-success-lumiere"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
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
               placeholder="••••••••" autocomplete="new-password" required>
      </div>
      <div class="mb-3">
        <label for="senha_confirm">Confirmar Senha</label>
        <input type="password" class="form-control" id="senha_confirm" name="senha_confirm"
               placeholder="••••••••" autocomplete="new-password" required>
      </div>
      <button type="submit" class="btn-lumiere">
        <i class="bi bi-person-plus me-2"></i>Cadastrar
      </button>
      <button type="button" class="btn-lumiere-outline" onclick="location.href='login.php'">
        <i class="bi bi-arrow-left me-2"></i>Voltar ao login
      </button>
    </form>

    <hr class="divider">
    <div class="hint">
      Já tem conta? <a href="login.php">Faça login aqui</a>
    </div>
  </div>
</div>
</body>
</html>
