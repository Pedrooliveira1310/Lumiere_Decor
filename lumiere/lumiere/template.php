
<?php
// template.php — call render_header() and render_footer()
require_once __DIR__ . '/config.php';
use Lumiere\Auth\Session;

function get_logo_image(): ?string {
    $logoPath = 'uploads/logo.png';
    return file_exists(__DIR__ . '/uploads/logo.png') ? $logoPath : null;
}

function render_header(string $activeNav = 'loja'): void {
    $user = Session::get('user');
    $username = htmlspecialchars($user['username'] ?? '');
    $isAdmin = Session::isAdmin();
    $navs = [
        'loja'   => ['href' => 'index.php',               'label' => 'Loja'],
        'sports' => ['href' => 'pacotes_esportivos.php',  'label' => 'Pacotes Esportivos'],
    ];
    if ($isAdmin) {
        $navs['admin']      = ['href' => 'admin.php',      'label' => 'Admin'];
        $navs['financeiro'] = ['href' => 'financeiro.php', 'label' => 'Ganhos & Devoluções'];
        unset($navs['sports'], $navs['loja']);
    }
    $logoHref = $isAdmin ? 'admin.php' : 'index.php';
    $logoImage = get_logo_image();
    $showTermsOverlay = Session::isLoggedIn() && !Session::isAdmin() && empty($user['accepted_terms']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lumière Decor</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
  --primary:    #6D5C3A;
  --primary-lt: #E8D1A7;
  --primary-lg: #F7E0B5;
  --secondary:  #F7E7CE;
  --secondary-m:#F7E7CE;
  --secondary-d:#B7A993;
  --bg:         #FFFEFD;
  --dark:       #141414;
  --dark-lt:    #2E2E2E;
  --light:      #FFFFFF;
  --light-m:    #819EB1;
  --light-d:    #4E6B7E;
  --success:    #52D65F;
  --success-d:  #27A533;
  --danger:     #D11C00;
  --border:     rgba(109,92,58,.15);
  --shadow:     0 4px 24px rgba(109,92,58,.10);
}
*, *::before, *::after { box-sizing: border-box; }
html {
  width: 100%;
  max-width: 100%;
  overflow-x: hidden;
  -webkit-text-size-adjust: 100%;
}
body {
  font-family: 'Montserrat', sans-serif;
  background: var(--bg);
  color: var(--dark);
  min-height: 100vh;
  width: 100%;
  max-width: 100%;
  overflow-x: hidden;
  margin: 0;
}
img, video, svg { max-width: 100%; height: auto; }
/* NAV */
.lum-nav {
  background: var(--light);
  border-bottom: 1px solid var(--border);
  padding: .75rem clamp(.75rem, 3vw, 2rem);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: .5rem .75rem;
  position: sticky;
  top: 0;
  z-index: 100;
  width: 100%;
}
.lum-nav-toggle {
  display: none;
  align-items: center;
  justify-content: center;
  width: 42px;
  height: 42px;
  padding: 0;
  border: 1px solid var(--border);
  border-radius: 2px;
  background: var(--bg);
  color: var(--primary);
  cursor: pointer;
  font-size: 1.25rem;
  flex-shrink: 0;
  margin-left: auto;
}
.lum-nav-toggle:hover { background: var(--primary-lt); }
.lum-logo {
  display: flex; align-items: center; gap: .6rem;
  text-decoration: none; color: var(--primary);
}
.lum-logo-icon {
  width: 120px; height: 100px;
  border-radius: 18px;
  display: flex; align-items: center; justify-content: center;
  overflow: hidden;
  font-size: .95rem; color: var(--primary);
}
.lum-logo-icon img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
}
.lum-logo-text {
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem; font-weight: 600;
  letter-spacing: .04em;
}
.lum-nav-links {
  display: flex;
  gap: clamp(1rem, 3vw, 2rem);
  list-style: none;
  margin: 0;
  padding: 0;
  flex-wrap: wrap;
}
.lum-nav-links a {
  font-size: .72rem; letter-spacing: .14em; text-transform: uppercase;
  font-weight: 600; color: var(--dark-lt); text-decoration: none;
  padding-bottom: .2rem; border-bottom: 2px solid transparent;
  transition: color .2s, border-color .2s;
}
.lum-nav-links a:hover { color: var(--primary); }
.lum-nav-links a.active { color: var(--primary); border-bottom-color: var(--primary); }
.lum-nav-right {
  display: flex;
  align-items: center;
  gap: .5rem .75rem;
  flex-wrap: wrap;
  justify-content: flex-end;
}
.lum-user {
  font-size: .78rem;
  color: var(--secondary-d);
  display: flex;
  align-items: center;
  gap: .4rem;
  max-width: 100%;
}
.lum-user strong { color: var(--primary); }
.btn-sair {
  font-size: .7rem; letter-spacing: .12em; text-transform: uppercase;
  font-weight: 600; color: var(--dark-lt); background: none; border: none;
  cursor: pointer; padding: .4rem .8rem;
  border: 1px solid var(--border); border-radius: 1px;
  transition: all .2s;
  display: flex; align-items: center; gap: .3rem;
}
.btn-sair:hover { background: var(--dark); color: var(--light); border-color: var(--dark); }
/* PAGE */
.lum-page {
  width: 100%;
  max-width: 1100px;
  margin: 0 auto;
  padding: clamp(1.25rem, 4vw, 2.5rem) clamp(.75rem, 3vw, 1.5rem);
}
.lum-page-header {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  justify-content: space-between;
  gap: .75rem 1rem;
}
.lum-page-header .lum-h1,
.lum-page-header .lum-h2 { margin: 0; }
/* FOOTER */
.lum-footer {
  border-top: 1px solid var(--border);
  padding: 2rem 2rem 1.5rem;
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 1rem;
  margin-top: 4rem;
}
.lum-footer-logo {
  font-family: 'Playfair Display', serif;
  font-size: .95rem; color: var(--primary);
  display: flex; align-items: center; gap: .5rem;
}
.lum-footer-copy { font-size: .72rem; color: var(--secondary-d); margin-top: .2rem; }
.lum-footer-links { display: flex; gap: 1.5rem; }
.lum-footer-links a { font-size: .72rem; color: var(--secondary-d); text-decoration: none; }
.lum-footer-links a:hover { color: var(--primary); }
/* HEADINGS */
.lum-h1 {
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.8rem, 4vw, 2.5rem);
  color: var(--primary); font-weight: 700; line-height: 1.2;
}
.lum-h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem; color: var(--dark);
}
.lum-label {
  font-size: .68rem; letter-spacing: .15em; text-transform: uppercase;
  font-weight: 600; color: var(--secondary-d);
}
/* CARD */
.lum-card {
  background: var(--light);
  border: 1px solid var(--border);
  border-radius: 2px;
  padding: 1.8rem;
  box-shadow: var(--shadow);
}
/* FORM CONTROLS */
.lum-label-field {
  font-size: .68rem; letter-spacing: .12em; text-transform: uppercase;
  font-weight: 600; color: var(--primary); margin-bottom: .35rem; display: block;
}
.lum-input {
  width: 100%;
  max-width: 100%;
  border: 1px solid var(--border);
  border-radius: 1px; padding: .7rem .9rem;
  font-family: 'Montserrat', sans-serif; font-size: .88rem;
  background: var(--bg); color: var(--dark);
  transition: border-color .2s, box-shadow .2s;
}
.lum-input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(109,92,58,.08);
  outline: none; background: var(--light);
}
select.lum-input { cursor: pointer; }
/* BUTTONS */
.btn-lum {
  border: none; cursor: pointer; font-family: 'Montserrat', sans-serif;
  font-size: .7rem; letter-spacing: .15em; text-transform: uppercase;
  font-weight: 600; border-radius: 1px; padding: .75rem 1.5rem;
  transition: all .2s; display: inline-flex; align-items: center; gap: .4rem;
}
.btn-lum-gold { background: var(--primary-lt); color: var(--primary); }
.btn-lum-gold:hover { background: var(--primary); color: var(--light); }
.btn-lum-dark { background: var(--dark); color: var(--light); }
.btn-lum-dark:hover { background: var(--dark-lt); }
.btn-lum-outline {
  background: none; border: 1px solid var(--border); color: var(--dark-lt);
}
.btn-lum-outline:hover { border-color: var(--primary); color: var(--primary); }
.btn-lum-danger { background: rgba(209,28,0,.08); color: var(--danger); border: 1px solid rgba(209,28,0,.2); }
.btn-lum-danger:hover { background: var(--danger); color: var(--light); }
.btn-lum-success { background: rgba(82,214,95,.1); color: var(--success-d); border: 1px solid rgba(82,214,95,.25); }
.btn-lum-success:hover { background: var(--success-d); color: var(--light); }
.btn-lum-sm { padding: .45rem .9rem; font-size: .65rem; }
/* STATUS BADGE */
.badge-status {
  font-size: .6rem; letter-spacing: .1em; text-transform: uppercase;
  font-weight: 600; padding: .3rem .7rem; border-radius: 100px;
}
.badge-disponivel { background: rgba(82,214,95,.12); color: var(--success-d); }
.badge-alugado    { background: rgba(232,209,167,.4); color: var(--primary); }
/* TABLE */
.lum-table { width: 100%; border-collapse: collapse; }
.lum-table th {
  font-size: .65rem; letter-spacing: .12em; text-transform: uppercase;
  font-weight: 600; color: var(--secondary-d);
  padding: .7rem 1rem; border-bottom: 1px solid var(--border);
  text-align: left; white-space: nowrap;
}
.lum-table td {
  padding: .9rem 1rem; border-bottom: 1px solid var(--border);
  font-size: .85rem; vertical-align: middle;
}
.lum-table tr:last-child td { border-bottom: none; }
.lum-table tr:hover td { background: rgba(247,231,206,.2); }
/* GRID DE CARDS — mesma altura em todas as colunas */
.lum-cards-row {
  align-items: stretch;
}
.lum-cards-row > [class*="col"] {
  display: flex;
}
.lum-cards-row .lum-product-card {
  height: auto;
  min-height: 440px;
}
.lum-product-card {
  width: 100%;
  height: auto;
  min-height: 440px;
  display: flex;
  flex-direction: column;
  padding: 0 !important;
  overflow: hidden;
}
.lum-product-card .lum-card-media {
  min-height: 240px;
  max-height: 280px;
  flex-shrink: 0;
  overflow: hidden;
  position: relative;
  background: var(--secondary);
  display: flex;
  align-items: center;
  justify-content: center;
}
.lum-product-card .lum-card-media.lum-card-media-dark {
  background: var(--dark);
}
.lum-product-card .lum-card-media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.lum-product-card .lum-card-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 1rem;
  min-height: 0;
}
.lum-product-card .lum-card-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.05rem;
  margin-bottom: .5rem;
  line-height: 1.35;
  color: var(--primary);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 2.7em;
}
.lum-product-card .lum-card-desc {
  font-size: .78rem;
  color: var(--secondary-d);
  line-height: 1.5;
  flex: 1;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 3.6em;
  margin-bottom: .75rem;
}
.lum-product-card .lum-card-meta {
  font-size: .75rem;
  color: var(--secondary-d);
  margin-bottom: .5rem;
  min-height: 1.2em;
}
.lum-product-card .lum-card-price {
  font-size: .8rem;
  color: var(--primary);
  font-weight: 600;
  margin-bottom: .75rem;
  flex-shrink: 0;
}
.lum-product-card .lum-card-footer {
  margin-top: auto;
  flex-shrink: 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: .5rem;
}
.lum-product-card .lum-card-actions {
  margin-top: auto;
  flex-shrink: 0;
}
.lum-card-hover {
  transition: transform .2s, box-shadow .2s;
}
.lum-card-hover:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(109, 92, 58, .15);
}
.lum-info-card {
  height: 100%;
  min-height: 168px;
  display: flex;
  flex-direction: column;
  padding: 1rem !important;
}
.lum-cards-row .lum-info-card {
  height: 168px;
}
.lum-info-card-actions {
  margin-top: auto;
  padding-top: .75rem;
}
/* TOAST */
.lum-toast-wrap {
  position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;
  display: flex; flex-direction: column; gap: .5rem;
}
.lum-toast {
  background: var(--dark); color: var(--light);
  border-radius: 2px; padding: .8rem 1.2rem;
  font-size: .8rem; font-family: 'Montserrat', sans-serif;
  box-shadow: 0 8px 24px rgba(0,0,0,.2);
  display: flex; align-items: center; gap: .6rem;
  animation: toastIn .3s ease;
  min-width: 220px;
}
.lum-toast.success { border-left: 3px solid var(--success); }
.lum-toast.error   { border-left: 3px solid var(--danger); }
@keyframes toastIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:none; } }
/* MODAL */
.lum-modal {
  position: fixed; inset: 0; z-index: 10000;
  display: flex; align-items: center; justify-content: center;
  padding: 1rem;
  opacity: 0; visibility: hidden;
  transition: opacity .2s, visibility .2s;
}
.lum-modal.open { opacity: 1; visibility: visible; }
.lum-modal-backdrop {
  position: absolute; inset: 0;
  background: rgba(20, 20, 20, .45);
  backdrop-filter: blur(2px);
}
.lum-modal-box {
  position: relative; z-index: 1;
  background: var(--light);
  border: 1px solid var(--border);
  border-radius: 2px;
  box-shadow: 0 20px 48px rgba(109, 92, 58, .2);
  width: 100%; max-width: 420px;
  animation: modalIn .25s ease;
}
@keyframes modalIn {
  from { opacity: 0; transform: translateY(12px) scale(.98); }
  to { opacity: 1; transform: none; }
}
.lum-modal-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  gap: 1rem; padding: 1.25rem 1.25rem .5rem;
}
.lum-modal-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem; color: var(--primary);
  margin: 0; font-weight: 600; line-height: 1.3;
}
.lum-modal-close {
  background: none; border: none; cursor: pointer;
  font-size: 1.4rem; line-height: 1; color: var(--secondary-d);
  padding: 0 .2rem; flex-shrink: 0;
}
.lum-modal-close:hover { color: var(--dark); }
.lum-modal-body {
  padding: 0 1.25rem 1rem;
  font-size: .88rem; color: var(--dark-lt); line-height: 1.6;
}
.lum-modal-details {
  margin-top: 1rem; padding: .85rem 1rem;
  background: var(--bg); border: 1px solid var(--border);
  border-radius: 2px;
}
.lum-modal-detail-row {
  display: flex; justify-content: space-between; gap: 1rem;
  font-size: .82rem; padding: .35rem 0;
}
.lum-modal-detail-row + .lum-modal-detail-row {
  border-top: 1px solid var(--border);
  margin-top: .35rem; padding-top: .65rem;
}
.lum-modal-detail-row span:first-child {
  color: var(--secondary-d); font-weight: 500;
}
.lum-modal-detail-row span:last-child {
  color: var(--primary); font-weight: 600; text-align: right;
}
.lum-modal-input-wrap { padding: 0 1.25rem 1rem; }
.lum-modal-actions {
  display: flex; gap: .6rem; justify-content: flex-end;
  padding: 1rem 1.25rem 1.25rem;
  border-top: 1px solid var(--border);
}
.lum-modal-actions .btn-lum-danger-confirm {
  background: rgba(209, 28, 0, .1); color: var(--danger);
  border: 1px solid rgba(209, 28, 0, .25);
}
.lum-modal-actions .btn-lum-danger-confirm:hover {
  background: var(--danger); color: var(--light);
}
.lum-term-accept-overlay {
  position: fixed;
  inset: 0;
  z-index: 11000;
  display: none;
  align-items: center;
  justify-content: center;
  background: rgba(20, 20, 20, .8);
  padding: 1rem;
}
.lum-term-accept-overlay.open {
  display: flex;
}
.lum-term-accept-box {
  width: min(100%, 980px);
  max-height: calc(100vh - 2rem);
  background: var(--light);
  border: 1px solid var(--border);
  border-radius: 2px;
  box-shadow: 0 22px 56px rgba(0, 0, 0, .15);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.lum-term-accept-header {
  padding: 1.25rem 1.25rem .75rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}
.lum-term-accept-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.35rem;
  margin: 0;
  color: var(--primary);
}
.lum-term-accept-body {
  flex: 1;
  min-height: 0;
  overflow: hidden;
}
.lum-term-accept-text {
  width: 100%;
  height: 100%;
  max-height: calc(100vh - 220px);
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  padding: 1rem 1.25rem 1rem;
  line-height: 1.75;
  color: var(--dark-lt);
  font-size: .95rem;
}
/* custom scrollbar for term box */
.lum-term-accept-text::-webkit-scrollbar { width: 10px; }
.lum-term-accept-text::-webkit-scrollbar-thumb { background: rgba(109,92,58,.18); border-radius: 6px; }
.lum-term-accept-text::-webkit-scrollbar-track { background: transparent; }
.lum-term-accept-text ul {
  margin: .75rem 0 1rem 1.25rem;
}
.lum-term-accept-text li {
  margin-bottom: .5rem;
}
.lum-term-accept-actions {
  padding: 1rem 1.25rem 1.25rem;
  border-top: 1px solid var(--border);
  display: flex;
  justify-content: flex-end;
}
/* HERO / SEÇÕES ESPECIAIS */
.lum-hero {
  text-align: center;
  padding: clamp(1.5rem, 5vw, 3rem) clamp(1rem, 4vw, 2rem) !important;
}
.lum-hero .lum-h1 {
  font-size: clamp(1.45rem, 5vw, 2.5rem) !important;
  line-height: 1.2;
}
.lum-hero p {
  font-size: clamp(.82rem, 2.5vw, .9rem) !important;
  max-width: 100% !important;
  margin-left: auto !important;
  margin-right: auto !important;
}
.lum-table-wrap {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
.lum-table { min-width: 520px; }

/* Desktop — menu em linha */
@media (min-width: 992px) {
  .lum-nav { flex-wrap: nowrap; }
  .lum-nav-links {
    flex: 1;
    justify-content: center;
    order: 0;
  }
  .lum-logo { flex-shrink: 0; }
  .lum-nav-right { flex-shrink: 0; }
}

/* RESPONSIVE — notebook (até 1200px) */
@media (max-width: 1200px) {
  .lum-page { max-width: 100%; }
  .lum-cards-row .lum-product-card { height: 400px; }
}

/* RESPONSIVE — tablet */
@media (max-width: 991px) {
  .lum-nav-toggle { display: inline-flex; }
  .lum-nav-links {
    display: none;
    flex-direction: column;
    align-items: stretch;
    flex-basis: 100%;
    width: 100%;
    order: 10;
    gap: 0;
    padding-top: .5rem;
    margin-top: .25rem;
    border-top: 1px solid var(--border);
  }
  .lum-nav-links.open { display: flex; }
  .lum-nav-links li { width: 100%; }
  .lum-nav-links a {
    display: block;
    padding: .75rem 0;
    border-bottom: none;
    border-left: 3px solid transparent;
  }
  .lum-nav-links a.active {
    border-left-color: var(--primary);
    border-bottom-color: transparent;
    padding-left: .5rem;
  }
  .lum-nav-right {
    order: 2;
    margin-left: 0;
  }
  .lum-logo { order: 1; flex: 1; min-width: 0; }
  .lum-nav-toggle { order: 2; margin-left: auto; }
  .lum-user .lum-user-text { display: none; }
  .lum-cards-row .lum-product-card { height: auto; min-height: 400px; }
  .lum-modal-actions {
    flex-direction: column-reverse;
    align-items: stretch;
  }
  .lum-modal-actions .btn-lum { width: 100%; justify-content: center; }
  .lum-footer {
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 1.5rem 1rem;
  }
  .lum-footer-links { flex-wrap: wrap; justify-content: center; }
}

/* RESPONSIVE — celular */
@media (max-width: 576px) {
  .lum-nav { padding: .65rem .75rem; }
  .lum-logo-text { font-size: 1rem; }
  .lum-logo-icon { width: 32px; height: 32px; font-size: .85rem; }
  .btn-sair span.lum-btn-label { display: none; }
  .btn-sair { padding: .45rem .55rem; }
  .lum-page { padding: 1rem .75rem; }
  .lum-card { padding: 1.15rem; }
  .lum-h1 { font-size: 1.45rem !important; }
  .lum-h2 { font-size: 1.2rem !important; }
  .lum-cards-row .lum-product-card {
    height: auto;
    min-height: 0;
  }
  .lum-product-card .lum-card-media { height: 150px; }
  .lum-product-card .lum-card-body { padding: 1rem; }
  .lum-product-card .lum-card-actions .d-flex {
    flex-direction: column;
    align-items: stretch !important;
  }
  .lum-product-card .lum-card-actions input.lum-input,
  .lum-product-card .lum-card-actions input[type="number"] {
    width: 100% !important;
  }
  .lum-product-card .lum-card-actions .btn-lum { width: 100%; }
  .lum-product-card .lum-card-footer {
    flex-direction: column;
    align-items: stretch;
    gap: .65rem;
  }
  .lum-product-card .lum-card-footer .btn-lum { width: 100%; justify-content: center; }
  .lum-cards-row .lum-info-card {
    height: auto;
    min-height: 0;
  }
  .lum-toast-wrap {
    left: .75rem;
    right: .75rem;
    bottom: .75rem;
  }
  .lum-toast { min-width: 0; width: 100%; }
  .lum-modal { padding: .5rem; align-items: flex-end; }
  .lum-modal-box {
    max-width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    border-radius: 2px 2px 0 0;
  }
  .lum-modal-detail-row {
    flex-direction: column;
    gap: .2rem;
    align-items: flex-start;
  }
  .lum-modal-detail-row span:last-child { text-align: left; }
  #filter-tabs .btn-lum { flex: 1 1 auto; min-width: 0; font-size: .6rem; padding: .4rem .5rem; }
  .lum-destaque-media { min-height: 180px !important; }
  .lum-destaque-body .btn-lum { width: 100%; justify-content: center; }
}
@media (min-width: 577px) and (max-width: 991px) {
  .lum-destaque-media { min-height: 200px !important; }
}
</style>
</head>
<body>

<nav class="lum-nav" id="lum-nav">
  <a href="<?= $logoHref ?>" class="lum-logo">
    <div class="lum-logo-icon">
      <?php if ($logoImage): ?>
        <img src="<?= htmlspecialchars($logoImage) ?>" alt="Logo Lumière">
      <?php else: ?>
        <i class="bi bi-stars"></i>
      <?php endif; ?>
    </div>
  </a>
  <button type="button" class="lum-nav-toggle" id="lum-nav-toggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="lum-nav-links">
    <i class="bi bi-list" id="lum-nav-toggle-icon"></i>
  </button>
  <ul class="lum-nav-links" id="lum-nav-links">
    <?php foreach ($navs as $key => $nav): ?>
    <li><a href="<?= $nav['href'] ?>" class="<?= $activeNav === $key ? 'active' : '' ?>"><?= $nav['label'] ?></a></li>
    <?php endforeach; ?>
  </ul>
  <div class="lum-nav-right">
    <span class="lum-user">
      <i class="bi bi-person-circle"></i>
      <span class="lum-user-text">Bem-vindo, <strong><?= $username ?></strong></span>
    </span>
    <form method="POST" action="logout.php" style="margin:0">
      <button type="submit" class="btn-sair" title="Sair">
        <i class="bi bi-box-arrow-right"></i><span class="lum-btn-label"> Sair</span>
      </button>
    </form>
  </div>
</nav>

<div id="lum-toast-wrap" class="lum-toast-wrap"></div>

<div id="lum-modal" class="lum-modal" aria-hidden="true">
  <div class="lum-modal-backdrop" data-lum-modal-close></div>
  <div class="lum-modal-box" role="dialog" aria-modal="true" aria-labelledby="lum-modal-title">
    <div class="lum-modal-header">
      <h3 id="lum-modal-title" class="lum-modal-title"></h3>
      <button type="button" class="lum-modal-close" data-lum-modal-close aria-label="Fechar">&times;</button>
    </div>
    <div id="lum-modal-message" class="lum-modal-body"></div>
    <div id="lum-modal-details" class="lum-modal-details" style="display:none;margin:0 1.25rem 1rem"></div>
    <div id="lum-modal-input-wrap" class="lum-modal-input-wrap" style="display:none">
      <label id="lum-modal-input-label" class="lum-label-field"></label>
      <input id="lum-modal-input" class="lum-input" type="text">
    </div>
    <div class="lum-modal-actions">
      <button type="button" id="lum-modal-cancel" class="btn-lum btn-lum-outline"></button>
      <button type="button" id="lum-modal-confirm" class="btn-lum btn-lum-gold"></button>
    </div>
  </div>
</div>

<div id="lum-term-accept-overlay" class="lum-term-accept-overlay" aria-hidden="true">
  <div class="lum-term-accept-box" role="dialog" aria-modal="true" aria-labelledby="lum-term-accept-title">
    <div class="lum-term-accept-header">
      <h3 id="lum-term-accept-title" class="lum-term-accept-title">Termo de responsabilidade</h3>
    </div>
    <div class="lum-term-accept-body">
      <div class="lum-term-accept-text">
        <p>INSTRUMENTO PARTICULAR DE TERMO DE RESPONSABILIDADE E</p>
        <p>CONDIÇÕES DE LOCAÇÃO DE ACERVO</p>
        <p>Pelo presente instrumento, de um lado, LUMIÈRE DECOR, plataforma web e fornecedora de acervos e cenografias de luxo, doravante denominada simplesmente LOCADORA, e, de outro lado, o usuário devidamente cadastrado e identificado por meio de suas credenciais de acesso no sistema web da plataforma, doravante denominado simplesmente LOCATÁRIO, celebram o presente Termo de Responsabilidade, mediante as cláusulas e condições contratuais a seguir expostas:</p>
        <p><strong>CLÁUSULA PRIMEIRA – DO OBJETO</strong></p>
        <p>1.1. O objeto deste termo é a locação por tempo determinado de bens móveis, peças decorativas, mobiliários e itens cenográficos de alto padrão (doravante denominados "Acervo"), selecionados e especificados pelo LOCATÁRIO através do Carrinho de Locação da plataforma da LOCADORA.</p>
        <p><strong>CLÁUSULA SEGUNDA – DA RESERVA E DO CÁLCULO DE DIÁRIAS</strong></p>
        <p>2.1. A reserva dos itens do Acervo será formalizada apenas após a confirmação do pagamento correspondente ao período de locação selecionado.</p>
        <p>2.2. O valor total da locação será calculado de forma automatizada pelo sistema web da LOCADORA, tendo como base o preço unitário da diária de cada peça multiplicado pela quantidade de dias solicitados pelo LOCATÁRIO.</p>
        <p>2.3. Caso o LOCATÁRIO necessite estender o período de locação, deverá solicitar uma nova previsão de diárias no sistema com antecedência mínima de 48 (quarenta e oito) horas do prazo final estipulado, ficando a prorrogação sujeita à disponibilidade do Acervo no estoque.</p>
        <p><strong>CLÁUSULA TERCEIRA – DA RETIRADA E DO ESTADO DOS BENS</strong></p>
        <p>3.1. O LOCATÁRIO declara receber os bens descritos no pedido em perfeito estado de conservação, limpeza e pleno funcionamento estético e estrutural, conforme registrado nas imagens institucionais e especificações técnicas da plataforma.</p>
        <p>3.2. É dever do LOCATÁRIO conferir minuciosamente cada item no ato do recebimento ou retirada, não sendo aceitas reclamações posteriores sobre avarias preexistentes que não tenham sido formalmente registradas junto à LOCADORA no momento da entrega.</p>
        <p><strong>CLÁUSULA QUARTA – DA RESPONSABILIDADE PELA GUARDA E USO DOS BENS</strong></p>
        <p>4.1. O LOCATÁRIO assume a responsabilidade civil e criminal exclusiva pela guarda, conservação e uso adequado dos itens locados, a partir do momento de sua retirada/entrega até a efetiva devolução e conferência física por parte da LOCADORA.</p>
        <p>4.2. Os itens do Acervo destinam-se estritamente à finalidade de ornamentação e ambientação do evento indicado, sendo expressamente proibido:</p>
        <ul>
          <li>Sublocar, ceder, emprestar ou transferir os bens a terceiros sem autorização prévia e por escrito da LOCADORA;</li>
          <li>Expor o mobiliário de luxo (como veludos, mármores e cristais) a condições climáticas adversas (chuva, umidade excessiva ou sol direto) sem a devida cobertura e proteção protetiva;</li>
          <li>Realizar modificações, pinturas, colagens ou reparos por conta própria nas peças locadas.</li>
        </ul>
        <p><strong>CLÁUSULA QUINTA – DA DEVOLUÇÃO, ATRASOS E MULTAS</strong></p>
        <p>5.1. O LOCATÁRIO obriga-se a devolver os bens locados na data, horário e local impreterivelmente estipulados no fechamento do pedido e sob controle do sistema da LOCADORA.</p>
        <p>5.2. O atraso na devolução ensejará a cobrança automática de novas diárias pelo período excedente, acrescida de multa moratória de 10% (dez por cento) sobre o valor total do contrato, sem prejuízo de eventuais indenizações por lucros cessantes decorrentes do cancelamento de reservas subsequentes do mesmo Acervo com outros clientes.</p>
        <p><strong>CLÁUSULA SEXTA – DOS DANOS, AVARIAS E EXTRAVIOS</strong></p>
        <p>6.1. Constatada qualquer avaria (manchas texturizadas, rasgos em tecidos, quebras em cristais, lascas em mármores ou cerâmicas) ou extravio total/parcial de qualquer peça do Acervo no ato da devolução, o LOCATÁRIO será integralmente responsável pelo ressarcimento financeiro à LOCADORA.</p>
        <p>6.2. Os valores para indenização seguirão os seguintes critérios:</p>
        <ul>
          <li>Danos reparáveis: O LOCATÁRIO arcará com o custo total do conserto efetuado por profissional especializado indicado pela LOCADORA, acrescido do valor das diárias em que o item permanecer indisponível para locação.</li>
          <li>Danos irreparáveis ou Extravio: O LOCATÁRIO pagará o valor de mercado atualizado para a reposição integral de uma peça idêntica ou equivalente de padrão de luxo, conforme tabela patrimonial interna da LOCADORA.</li>
        </ul>
        <p><strong>CLÁUSULA SÉTIMA – DA POLÍTICA DE CANCELAMENTO</strong></p>
        <p>7.1. O cancelamento da locação por iniciativa do LOCATÁRIO observará as penalidades proporcionais à antecedência da data do evento, destinadas a cobrir os custos de reserva de estoque, cujos percentuais de retenção financeira seguem os parâmetros legais vigentes de proteção ao equilíbrio contratual.</p>
        <p><strong>CLÁUSULA OITAVA – DO FORO</strong></p>
        <p>8.1. Para dirimir quaisquer dúvidas ou controvérsias oriundas do presente Termo, as partes elegem o Foro da Comarca da LOCADORA, com expressa renúncia a qualquer outro, por mais privilegiado que seja.</p>
        <p>O LOCATÁRIO declara expressamente ter lido, compreendido e aceitado todos os termos e condições deste instrumento, anuindo eletronicamente ao clicar no botão de confirmação e finalização do Carrinho de Locação no sistema web da Lumière Decor.</p>
      </div>
    </div>
    <div class="lum-term-accept-actions">
      <button type="button" id="lum-term-accept-button" class="btn-lum btn-lum-gold">Aceitar</button>
    </div>
  </div>
</div>

<script>
const lumShowTermsOverlay = <?= $showTermsOverlay ? 'true' : 'false' ?>;
let _lumModalResolve = null;

function closeLumModal(result) {
  const modal = document.getElementById('lum-modal');
  modal.classList.remove('open');
  modal.setAttribute('aria-hidden', 'true');
  document.body.style.overflow = '';
  if (_lumModalResolve) {
    const fn = _lumModalResolve;
    _lumModalResolve = null;
    fn(result);
  }
}

function showLumModal(opts) {
  return new Promise((resolve) => {
    _lumModalResolve = resolve;
    const modal = document.getElementById('lum-modal');
    const titleEl = document.getElementById('lum-modal-title');
    const msgEl = document.getElementById('lum-modal-message');
    const detailsEl = document.getElementById('lum-modal-details');
    const inputWrap = document.getElementById('lum-modal-input-wrap');
    const inputLabel = document.getElementById('lum-modal-input-label');
    const inputEl = document.getElementById('lum-modal-input');
    const btnCancel = document.getElementById('lum-modal-cancel');
    const btnConfirm = document.getElementById('lum-modal-confirm');

    titleEl.textContent = opts.title || 'Confirmar';
    msgEl.textContent = opts.message || '';
    msgEl.style.display = opts.message ? 'block' : 'none';

    if (opts.details && opts.details.length) {
      detailsEl.style.display = 'block';
      detailsEl.innerHTML = opts.details.map(d =>
        `<div class="lum-modal-detail-row"><span>${d.label}</span><span>${d.value}</span></div>`
      ).join('');
    } else {
      detailsEl.style.display = 'none';
      detailsEl.innerHTML = '';
    }

    if (opts.input) {
      inputWrap.style.display = 'block';
      inputLabel.textContent = opts.input.label || '';
      inputEl.type = opts.input.type || 'number';
      inputEl.min = opts.input.min ?? 1;
      inputEl.value = opts.input.default ?? '1';
      setTimeout(() => inputEl.focus(), 100);
    } else {
      inputWrap.style.display = 'none';
    }

    btnCancel.textContent = opts.cancelText || 'Cancelar';
    btnConfirm.textContent = opts.confirmText || 'Confirmar';
    btnConfirm.className = 'btn-lum ' + (opts.danger ? 'btn-lum-danger-confirm' : 'btn-lum-gold');

    const onConfirm = () => {
      if (opts.input) {
        const val = inputEl.value.trim();
        if (opts.input.type === 'number' || inputEl.type === 'number') {
          const n = parseInt(val, 10);
          if (!n || n < (opts.input.min ?? 1)) {
            showToast('Valor inválido.', 'error');
            return;
          }
          closeLumModal({ ok: true, value: n });
        } else {
          closeLumModal({ ok: true, value: val });
        }
      } else {
        closeLumModal({ ok: true });
      }
    };

    btnConfirm.onclick = onConfirm;
    btnCancel.onclick = () => closeLumModal({ ok: false });
    inputEl.onkeydown = (e) => { if (e.key === 'Enter') { e.preventDefault(); onConfirm(); } };

    modal.querySelectorAll('[data-lum-modal-close]').forEach(el => {
      el.onclick = () => closeLumModal({ ok: false });
    });

    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  });
}

function lumConfirm(message, opts = {}) {
  return showLumModal({
    title: opts.title || 'Confirmar',
    message,
    details: opts.details,
    danger: opts.danger,
    confirmText: opts.confirmText || 'Confirmar',
    cancelText: opts.cancelText || 'Cancelar',
  }).then(r => r.ok);
}

function showToast(msg, type='success') {
  const w = document.getElementById('lum-toast-wrap');
  const t = document.createElement('div');
  t.className = `lum-toast ${type}`;
  t.innerHTML = `<i class="bi bi-${type==='success'?'check-circle':'exclamation-circle'}"></i>${msg}`;
  w.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}

async function apiPost(data) {
  const fd = new FormData();
  for (const [k,v] of Object.entries(data)) fd.append(k, v);
  const r = await fetch('api.php', {method:'POST', body: fd});
  return r.json();
}

function showTermsOverlay() {
  const overlay = document.getElementById('lum-term-accept-overlay');
  if (!overlay) return;
  overlay.classList.add('open');
  overlay.setAttribute('aria-hidden', 'false');
  document.body.style.overflow = 'hidden';
}

function closeTermsOverlay() {
  const overlay = document.getElementById('lum-term-accept-overlay');
  if (!overlay) return;
  overlay.classList.remove('open');
  overlay.setAttribute('aria-hidden', 'true');
  document.body.style.overflow = '';
}

async function acceptTerms() {
  const button = document.getElementById('lum-term-accept-button');
  if (!button) return;
  button.disabled = true;
  const response = await apiPost({ action: 'acceptTerms' });
  button.disabled = false;
  if (response.success) {
    closeTermsOverlay();
    showToast('Termo aceito. Obrigado.');
  } else {
    showToast(response.error || 'Erro ao aceitar termo.', 'error');
  }
}

(function initMobileNav() {
  const nav = document.getElementById('lum-nav');
  const toggle = document.getElementById('lum-nav-toggle');
  const links = document.getElementById('lum-nav-links');
  const icon = document.getElementById('lum-nav-toggle-icon');
  if (!nav || !toggle || !links) return;

  const termAcceptButton = document.getElementById('lum-term-accept-button');
  if (termAcceptButton) {
    termAcceptButton.addEventListener('click', acceptTerms);
  }

  document.addEventListener('click', (event) => {
    const termLink = event.target.closest('.lum-term-link');
    if (!termLink) return;
    event.preventDefault();
    showTermsOverlay();
  });

  if (lumShowTermsOverlay) {
    showTermsOverlay();
  }

  toggle.addEventListener('click', () => {
    const open = links.classList.toggle('open');
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (icon) {
      icon.className = open ? 'bi bi-x-lg' : 'bi bi-list';
    }
  });

  links.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', () => {
      links.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
      if (icon) icon.className = 'bi bi-list';
    });
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth > 991) {
      links.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
      if (icon) icon.className = 'bi bi-list';
    }
  });
})();
</script>
<?php
}

function render_footer(): void {
    $logoImage = get_logo_image();
?>
<footer id="termo-responsabilidade" class="lum-footer">
  <div>
    <div class="lum-footer-logo">
      <?php if (!empty($logoImage)): ?>
        <img src="<?= htmlspecialchars($logoImage) ?>" alt="Logo Lumière" style="width: 120px;height: 80px;object-fit:contain;">
      <?php else: ?>
        <i class="bi bi-stars" style="color:var(--primary)"></i>
      <?php endif; ?>
    </div>
    <div class="lum-footer-copy">© 2026 Lumière Decor. Todos os direitos reservados.</div>
    <div style="font-size:.72rem;color:var(--secondary-d);margin-top:.85rem;max-width:360px;">
      Ao devolver um item, o cliente confirma que leu e aceitou o Termo de responsabilidade.</div>
  </div>
  <div class="lum-footer-links">
    <a href="#" class="lum-term-link">Termo de responsabilidade</a>
    <a href="#">Política de Privacidade</a>
    <a href="#">Sustentabilidade</a>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
}
