<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/template.php';
use Lumiere\Auth\Session;
use Lumiere\Models\Pacote;
Session::requireLogin();
if (Session::isAdmin()) {
    header('Location: financeiro.php');
    exit;
}
render_header('sports');
?>
<div class="lum-page">

  <!-- HERO -->
  <?php $heroPacote = Pacote::findById('vem-de-hexa'); ?>
  <div class="lum-card lum-hero mb-5" style="background:var(--secondary);color:#6A5937">
    <span style="font-size:.68rem;letter-spacing:.22em;text-transform:uppercase;font-weight:600;background:var(--primary-lt);color:var(--primary);padding:.3rem .9rem;border-radius:100px;display:inline-block;margin-bottom:1rem">
      Coleções Especiais
    </span>
    <h1 class="lum-h1" style="color:#6A5937;margin-bottom:.8rem">A Arte de Torcer</h1>
    <p style="color:#6A5937;opacity:.85;max-width:500px;margin:0 auto">
      Descubra nossa seleção dinâmica de pacotes para eventos esportivos. A energia da arquibancada com o conforto que você merece para celebrar grandes momentos com os amigos.
    </p>
  </div>

  <!-- DESTAQUE -->
  <div class="lum-card mb-5 p-0" style="overflow:hidden">
    <div class="row g-0">
      <div class="col-12 col-md-5 lum-destaque-media" style="background:var(--secondary);min-height:220px;display:flex;align-items:center;justify-content:center;overflow:hidden">
        <?php if (!empty($heroPacote['imagem'])): ?>
        <img src="<?= htmlspecialchars($heroPacote['imagem']) ?>" alt="<?= htmlspecialchars($heroPacote['nome']) ?>" style="width:100%;height:100%;object-fit:cover;display:block;">
        <?php else: ?>
        <i class="bi bi-trophy" style="font-size:clamp(3rem,12vw,5rem);color:var(--primary-lt)"></i>
        <?php endif; ?>
      </div>
      <div class="col-12 col-md-7 lum-destaque-body" style="background:var(--dark);padding:clamp(1.25rem,4vw,2.5rem);color:var(--light)">
        <div style="font-size:.68rem;letter-spacing:.15em;text-transform:uppercase;font-weight:600;color:var(--primary-lt);margin-bottom:.5rem">
          Fan Lounge · 15–30 Convidados
        </div>
        <h2 style="font-family:'Playfair Display',serif;font-size:clamp(1.35rem,4vw,2rem);color:var(--primary-lt);margin-bottom:1rem">Vem de Hexa</h2>
        <p style="font-size:.85rem;color:rgba(255,255,255,.75);line-height:1.7;margin-bottom:1.5rem">
          Traga a energia do estádio para sua sala. O pacote "Vem de Hexa" oferece o cenário perfeito para a torcida com conforto máximo. Pufes modulares, coolers integrados e decoração temática vibrante em verde e amarelo.
        </p>
        <ul style="list-style:none;padding:0;margin-bottom:1.8rem">
          <?php foreach (['Assentos Modulares Descontraídos','Estação de Bebidas e Coolers','Decoração Temática Vibrante'] as $item): ?>
          <li style="font-size:.82rem;color:rgba(255,255,255,.8);margin-bottom:.4rem;display:flex;align-items:center;gap:.5rem">
            <i class="bi bi-check-circle-fill" style="color:#52D65F"></i><?= $item ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <button class="btn-lum btn-lum-gold" onclick="selecionarPacote('vem-de-hexa', 'Vem de Hexa', <?= Pacote::getValor('vem-de-hexa') ?? 2500 ?>)">
          <i class="bi bi-bag-check"></i> Garantir Meu Pacote
        </button>
      </div>
    </div>
  </div>

  <!-- GRID PACOTES -->
  <div class="mb-3 d-flex align-items-baseline justify-content-between">
    <h2 class="lum-h1" style="font-size:1.8rem">Convoque Sua Galera</h2>
  </div>
  <p style="font-size:.82rem;color:var(--secondary-d);margin-bottom:1.5rem">Escolha o tamanho da sua torcida</p>

  <!-- FILTER TABS -->
  <div class="d-flex gap-2 mb-3 flex-wrap" id="filter-tabs">
    <button class="btn-lum btn-lum-dark btn-lum-sm active-tab" data-filter="todos" onclick="filterPacotes(this,'todos')">Todos</button>
    <button class="btn-lum btn-lum-outline btn-lum-sm" data-filter="pequeno" onclick="filterPacotes(this,'pequeno')">Resenha (Pequeno)</button>
    <button class="btn-lum btn-lum-outline btn-lum-sm" data-filter="grande" onclick="filterPacotes(this,'grande')">Galera (Grande)</button>
  </div>

  <div class="row g-3 lum-cards-row mb-5" id="pacotes-grid">
    <?php
    $pacotesDb = Pacote::all();
    $pacotesEsportes = array_filter($pacotesDb, fn($p) => ($p['categoria'] ?? 'esporte') === 'esporte');
    
    $mapaExtra = [
      'vem-de-hexa' => ['desc'=>'Traga a energia do estádio para sua sala. Pufes modulares, coolers integrados e decoração vibrante.', 'size'=>'grande', 'icon'=>'bi-trophy'],
      'fan-fest-30' => ['desc'=>'Estrutura para 30 pessoas com telão, iluminação profissional e espaço confortável.', 'size'=>'grande', 'icon'=>'bi-people-fill'],
      'fan-fest-40' => ['desc'=>'Estrutura para 40 pessoas com telão grande, iluminação profissional e zona de conforto máximo.', 'size'=>'grande', 'icon'=>'bi-people-fill'],
      'fan-fest-50' => ['desc'=>'Estrutura para 50 pessoas com telão de cinema, iluminação de arena e comodidades premium.', 'size'=>'grande', 'icon'=>'bi-people-fill'],
      'fan-fest-60' => ['desc'=>'Estrutura para 60 pessoas com telão gigante, iluminação profissional e bar de petiscos.', 'size'=>'grande', 'icon'=>'bi-people-fill'],
    ];
    
    foreach ($pacotesEsportes as $idx => $p):
      $extra = $mapaExtra[$p['id']] ?? [];
      $desc = $extra['desc'] ?? 'Solicite mais informações.';
      $size = $extra['size'] ?? 'grande';
      $icon = $extra['icon'] ?? 'bi-star';
    ?>
    <div class="col-12 col-sm-6 col-md-4 pacote-card" data-size="<?= $size ?>">
      <div class="lum-card lum-product-card lum-card-hover">
        <div class="lum-card-media<?= !empty($p['imagem']) ? '' : ' lum-card-media-dark' ?>">
          <?php if (!empty($p['imagem'])): ?>
          <img src="<?= htmlspecialchars($p['imagem']) ?>" alt="<?= htmlspecialchars($p['nome']) ?>">
          <?php else: ?>
          <i class="bi <?= $icon ?>" style="font-size:3rem;color:var(--primary-lt)"></i>
          <?php endif; ?>
          <span style="position:absolute;top:.7rem;left:.7rem;background:rgba(82,214,95,.2);color:#27A533;font-size:.6rem;letter-spacing:.1em;text-transform:uppercase;font-weight:700;padding:.25rem .6rem;border-radius:100px">
            <?= htmlspecialchars($p['capacidade']) ?>
          </span>
        </div>
        <div class="lum-card-body">
          <div class="lum-card-title"><?= htmlspecialchars($p['nome']) ?></div>
          <div class="lum-card-desc"><?= htmlspecialchars($desc) ?></div>
          <div class="lum-card-footer">
            <span style="font-family:'Playfair Display',serif;font-size:1.05rem;color:var(--primary);font-weight:600">
              R$ <?= number_format($p['valor'], 2, ',', '.') ?>
            </span>
            <button class="btn-lum btn-lum-gold btn-lum-sm" onclick="selecionarPacote('<?= $p['id'] ?>', '<?= addslashes($p['nome']) ?>', <?= $p['valor'] ?>)" style="cursor:pointer">
              <i class="bi bi-bag-check"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- CTA CUSTOMIZADO -->
  <div class="lum-card text-center py-5">
    <i class="bi bi-chat-square-heart" style="font-size:2.5rem;color:var(--primary-lt);margin-bottom:1rem;display:block"></i>
    <h3 style="font-family:'Playfair Display',serif;font-size:1.5rem;color:var(--dark);margin-bottom:.5rem">
      Quer montar sua própria Fan Fest?
    </h3>
    <p style="font-size:.85rem;color:var(--secondary-d);max-width:420px;margin:0 auto 1.5rem">
      Nossa equipe monta o evento do zero com a cara da sua torcida. Do telão aos petiscos, a gente cuida de tudo pra você focar só no jogo.
    </p>
    <a href="https://wa.me/5511999999999" target="_blank" class="btn-lum btn-lum-gold" style="text-decoration:none">
      <i class="bi bi-whatsapp"></i> Chamar no WhatsApp
    </a>
  </div>

</div>

<?php render_footer(); ?>

<script>
function filterPacotes(btn, filter) {
  document.querySelectorAll('#filter-tabs button').forEach(b => {
    b.classList.remove('btn-lum-dark','active-tab');
    b.classList.add('btn-lum-outline');
  });
  btn.classList.add('btn-lum-dark','active-tab');
  btn.classList.remove('btn-lum-outline');

  document.querySelectorAll('.pacote-card').forEach(c => {
    c.style.display = (filter === 'todos' || c.dataset.size === filter) ? '' : 'none';
  });
}

async function selecionarPacote(id, nome, valor) {
  const valorFormatado = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valor);

  const step1 = await showLumModal({
    title: 'Alugar pacote',
    message: 'Informe por quantos dias deseja alugar este pacote.',
    details: [
      { label: 'Pacote', value: nome },
      { label: 'Valor', value: valorFormatado },
    ],
    input: { label: 'Quantos dias deseja alugar?', default: 1, min: 1, type: 'number' },
    confirmText: 'Continuar',
    cancelText: 'Cancelar',
  });
  if (!step1.ok) return;
  const dias = step1.value;

  const ok = await lumConfirm(
    `Você confirma o aluguel por ${dias} dia(s)?`,
    {
      title: 'Confirmar aluguel',
      details: [
        { label: 'Pacote', value: nome },
        { label: 'Duração', value: dias + ' dia(s)' },
        { label: 'Valor', value: valorFormatado },
      ],
      confirmText: 'Confirmar aluguel',
    }
  );
  if (!ok) return;

  const res = await apiPost({action:'alugarPacote', id, dias});
  if (res.success) {
    showToast('Pacote alugado com sucesso!', 'success');
    setTimeout(() => location.href = 'index.php', 1200);
  } else {
    showToast(res.error || 'Erro ao alugar pacote.', 'error');
  }
}
</script>

