<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/template.php';

use Lumiere\Auth\Session;
use Lumiere\Models\Item;

Session::requireLogin();

if (Session::isAdmin()) {
    header('Location: admin.php');
    exit;
}

$username  = Session::get('user')['username'];
$tipos     = Item::getTipos();
$items     = Item::getDisponiveis();
$meusAlugados = Item::getAlugadosPorUsuario($username);
$historico = Item::getHistoricoPorUsuario($username);

render_header('loja');
?>
<div class="lum-page">

  <!-- MEUS ALUGUÉIS ATIVOS -->
  <?php if (!empty($meusAlugados)): ?>
  <section class="lum-card mb-5">
    <div class="d-flex align-items-baseline justify-content-between mb-3">
      <div>
        <h2 class="lum-h2" style="font-family:'Playfair Display',serif">Meus aluguéis ativos</h2>
        <p style="font-size:.82rem;color:var(--secondary-d);margin-top:.3rem">
          Itens que você alugou. Quando devolver, eles voltam ao catálogo.
        </p>      <div style="font-size:.8rem;color:var(--secondary-d);margin-top:.75rem;max-width:700px">
        Ao solicitar devolução, você confirma que leu e aceita o Termo de responsabilidade e se compromete a devolver o produto em bom estado.
      </div>      </div>
      <span class="lum-label"><?= count($meusAlugados) ?> ativo(s)</span>
    </div>
    <div class="row g-3 lum-cards-row">
      <?php foreach ($meusAlugados as $item):
        $valor = Item::getDiariaFromItem($item) * max(1, (int)($item['dias'] ?? 1));
        $retorno = date('d/m/Y', strtotime(($item['data_aluguel'] ?? 'today') . ' + ' . (int)($item['dias'] ?? 1) . ' days'));
      ?>
      <div class="col-12 col-md-6" id="ativo-<?= $item['id'] ?>">
        <div class="lum-card lum-info-card w-100">
          <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap flex-grow-1">
            <div>
              <div style="font-weight:700;color:var(--primary);margin-bottom:.2rem"><?= htmlspecialchars($item['modelo']) ?></div>
              <div style="font-size:.82rem;color:var(--secondary-d)">
                <?= htmlspecialchars($tipos[$item['tipo']] ?? $item['tipo']) ?> • <?= (int)($item['dias'] ?? 0) ?> dia(s)
              </div>
              <div style="font-size:.78rem;color:var(--secondary-d);margin-top:.4rem">
                Retorno previsto: <?= $retorno ?>
              </div>
            </div>
            <div style="text-align:right">
              <div style="font-size:.8rem;color:var(--secondary-d)">Total</div>
              <div style="font-size:1rem;font-weight:700;color:var(--dark)">
                R$ <?= number_format($valor, 2, ',', '.') ?>
              </div>
            </div>
          </div>
          <div class="lum-info-card-actions">
            <button class="btn-lum btn-lum-success btn-lum-sm w-100" onclick="devolverItem(<?= $item['id'] ?>)">
              <i class="bi bi-arrow-return-left"></i> Devolver compra
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- HISTÓRICO DE ALUGUÉIS -->
  <section class="lum-card mb-5">
    <div class="d-flex align-items-baseline justify-content-between mb-3">
      <div>
        <h2 class="lum-h2" style="font-family:'Playfair Display',serif">Seu histórico de aluguel</h2>
        <p style="font-size:.82rem;color:var(--secondary-d);margin-top:.3rem">
          Registros do que você já alugou com valor total por dia e duração.
        </p>
      </div>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="lum-label"><?= count($historico) ?> registros</span>
        <?php if (!empty($historico)): ?>
        <button class="btn-lum btn-lum-danger btn-lum-sm" onclick="limparHistorico()">
          <i class="bi bi-trash3"></i> Apagar meu histórico
        </button>
        <?php endif; ?>
      </div>
    </div>
    <?php if (empty($historico)): ?>
    <div style="color:var(--secondary-d);font-size:.88rem">
      Você ainda não alugou nenhum item. Faça sua primeira reserva e ela aparecerá aqui.
    </div>
    <?php else: ?>
    <div class="row g-3 lum-cards-row">
      <?php foreach (array_reverse($historico) as $entry): ?>
      <div class="col-12 col-md-6">
        <div class="lum-card lum-info-card w-100">
          <div class="d-flex justify-content-between flex-wrap flex-grow-1" style="gap:1rem">
            <div>
              <div style="font-weight:700;color:var(--primary);margin-bottom:.2rem"><?= htmlspecialchars($entry['modelo']) ?></div>
              <div style="font-size:.82rem;color:var(--secondary-d);margin-bottom:.4rem">
                <?= htmlspecialchars($tipos[$entry['tipo']] ?? $entry['tipo']) ?> • <?= htmlspecialchars($entry['dias']) ?> dia(s)
              </div>
            </div>
            <div style="text-align:right;min-width:110px">
              <div style="font-size:.8rem;color:var(--secondary-d)">Total</div>
              <div style="font-size:1rem;font-weight:700;color:var(--dark)">
                R$ <?= number_format($entry['valor_total'], 2, ',', '.') ?>
              </div>
            </div>
          </div>
          <div style="font-size:.78rem;color:var(--secondary-d);margin-top:.75rem">
            Alugado em <?= date('d/m/Y H:i', strtotime($entry['rented_at'])) ?>
            <?php if (!empty($entry['returned_at'])): ?>
            · Devolvido em <?= date('d/m/Y H:i', strtotime($entry['returned_at'])) ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>

  <!-- PREVISÃO DE ALUGUEL -->
  <section class="lum-card mb-5">
    <div class="row align-items-end g-3">
      <div class="col-12 col-md-5">
        <h2 class="lum-h1" style="font-size:1.8rem">Previsão de Aluguel</h2>
        <p style="font-size:.82rem;color:var(--secondary-d);margin-top:.3rem">
          Calcule seu aluguel de produtos com precisão
        </p>
      </div>
      <div class="col-6 col-md-2">
        <label class="lum-label-field">Categoria</label>
        <select id="prev-tipo" class="lum-input">
          <option value="">Selecione</option>
          <?php foreach ($tipos as $key => $label): ?>
          <option value="<?= $key ?>"><?= $label ?> — R$<?= number_format(Item::getDiaria($key), 2, ',', '.') ?>/dia</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="lum-label-field">Duração (dias)</label>
        <input type="number" id="prev-dias" class="lum-input" min="1" value="1" placeholder="1">
      </div>
      <div class="col-6 col-md-2">
        <button class="btn-lum btn-lum-gold w-100" onclick="calcularPrevisao()">
          <i class="bi bi-calculator"></i> Calcular
        </button>
      </div>
      <div class="col-6 col-md-1">
        <div id="prev-resultado" style="display:none">
          <div class="lum-label-field">Estimativa</div>
          <div id="prev-valor" style="font-family:'Playfair Display',serif;font-size:1.3rem;color:var(--primary);font-weight:700"></div>
        </div>
      </div>
    </div>
  </section>

  <!-- PACOTES ESPECIAIS (CASAMENTO, FESTAS, FORMATURAS) -->
  <section class="mb-5">
    <div class="d-flex align-items-baseline justify-content-between mb-1">
      <h2 class="lum-h1">Pacotes Especiais</h2>
    </div>
    <p style="font-size:.82rem;color:var(--secondary-d);margin-bottom:1.5rem">
      Explore nossos pacotes completos para casamentos, festas, formaturas e eventos corporativos.
    </p>

    <?php
    use Lumiere\Models\Pacote;
    $pacotesDb = Pacote::all();
    $pacotesLoja = array_filter($pacotesDb, fn($p) => ($p['categoria'] ?? '') !== 'esporte');
    ?>

    <?php if (empty($pacotesLoja)): ?>
    <div class="lum-card text-center py-4" style="color:var(--secondary-d)">
      <i class="bi bi-gift" style="font-size:2rem"></i>
      <p style="margin-top:.5rem;font-size:.85rem">Nenhum pacote disponível no momento.</p>
    </div>
    <?php else: ?>
    <div class="row g-3 lum-cards-row mb-5" id="pacotes-grid">
      <?php foreach ($pacotesLoja as $p): ?>
      <div class="col-12 col-sm-6 col-md-4">
        <div class="lum-card lum-product-card">
          <div class="lum-card-media<?= !empty($p['imagem']) ? '' : ' lum-card-media-dark' ?>">
            <?php if (!empty($p['imagem'])): ?>
            <img src="<?= htmlspecialchars($p['imagem']) ?>" alt="<?= htmlspecialchars($p['nome']) ?>">
            <?php else: ?>
            <i class="bi bi-gift" style="font-size:3rem;color:var(--primary-lt)"></i>
            <?php endif; ?>
            <span style="position:absolute;top:.7rem;right:.7rem;background:var(--primary-lt);color:var(--primary);font-size:.6rem;letter-spacing:.1em;text-transform:uppercase;font-weight:700;padding:.25rem .6rem;border-radius:100px">
              <?= htmlspecialchars($p['capacidade']) ?>
            </span>
          </div>
          <div class="lum-card-body">
            <div class="lum-card-title"><?= htmlspecialchars($p['nome']) ?></div>
            <div class="lum-card-price">
              R$ <?= number_format($p['valor'], 2, ',', '.') ?>
            </div>
            <div class="lum-card-actions">
              <button class="btn-lum btn-lum-gold w-100" onclick="entrarEmContato('<?= htmlspecialchars($p['nome']) ?>')">
                <i class="bi bi-telephone"></i> Solicitar Informações
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>

  <!-- PRODUTOS DISPONÍVEIS -->
  <section>
    <div class="d-flex align-items-baseline justify-content-between mb-1">
      <h2 class="lum-h1">Produtos Disponíveis</h2>
      <span class="lum-label"><?= count($items) ?> itens</span>
    </div>
    <p style="font-size:.82rem;color:var(--secondary-d);margin-bottom:1.5rem">
      Explore nossos produtos disponíveis. Itens já alugados não aparecem aqui.
    </p>

    <?php if (empty($items)): ?>
    <div class="lum-card text-center py-4" style="color:var(--secondary-d)">
      <i class="bi bi-inbox" style="font-size:2rem"></i>
      <p style="margin-top:.5rem;font-size:.85rem">Nenhum produto disponível no momento.</p>
    </div>
    <?php else: ?>
    <div class="row g-3 lum-cards-row" id="cards-grid">
      <?php foreach ($items as $item): ?>
      <div class="col-12 col-sm-6 col-md-4" id="card-<?= $item['id'] ?>">
        <div class="lum-card lum-product-card">
          <?php $img = Item::getImageUrl($item); ?>
          <div class="lum-card-media">
            <?php if ($img): ?>
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item['modelo']) ?>">
            <?php else: ?>
            <i class="bi bi-image" style="font-size:2rem;color:var(--secondary-d)"></i>
            <?php endif; ?>
            <span style="position:absolute;top:.7rem;right:.7rem" class="badge-status badge-disponivel">Disponível</span>
          </div>
          <div class="lum-card-body">
            <div class="lum-label" style="margin-bottom:.3rem"><?= htmlspecialchars($tipos[$item['tipo']] ?? $item['tipo']) ?></div>
            <div class="lum-card-title"><?= htmlspecialchars($item['modelo']) ?></div>
            <div class="lum-card-desc"><?= htmlspecialchars($item['detalhes']) ?></div>
            <div class="lum-card-price">
              R$ <?= number_format(Item::getDiariaFromItem($item), 2, ',', '.') ?> / dia
            </div>
            <div class="lum-card-actions">
              <div class="d-flex gap-2 align-items-center">
                <input type="number" class="lum-input" style="width:70px;padding:.4rem .5rem;font-size:.8rem;font-family:Playfair Display,serif"
                       min="1" value="1" id="dias-<?= $item['id'] ?>" data-diaria="<?= Item::getDiariaFromItem($item) ?>">
                <button class="btn-lum btn-lum-gold btn-lum-sm flex-grow-1"
                        onclick="alugarItem(<?= $item['id'] ?>)">
                  <i class="bi bi-bag-check"></i> Alugar Item
                </button>
              </div>
              <div id="total-<?= $item['id'] ?>" style="font-size:.82rem;color:var(--dark);margin-top:.65rem">
                Total: R$ <?= number_format(Item::getDiariaFromItem($item), 2, ',', '.') ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>

</div>

<?php render_footer(); ?>

<script>
async function calcularPrevisao() {
  const tipo = document.getElementById('prev-tipo').value;
  const dias = document.getElementById('prev-dias').value;
  if (!tipo || !dias) { showToast('Selecione categoria e dias.', 'error'); return; }
  const res = await apiPost({action:'previsao', tipo, dias});
  if (res.success) {
    const el = document.getElementById('prev-resultado');
    document.getElementById('prev-valor').textContent =
      'R$ ' + res.valor.toLocaleString('pt-BR', {minimumFractionDigits:2});
    el.style.display = 'block';
  } else {
    showToast(res.error, 'error');
  }
}

async function alugarItem(id) {
  const dias = document.getElementById(`dias-${id}`)?.value || 1;
  const res = await apiPost({action:'alugar', id, dias});
  if (res.success) {
    showToast('Item alugado com sucesso!', 'success');
    setTimeout(() => location.reload(), 1200);
  } else {
    showToast(res.error, 'error');
  }
}

function formatCurrency(value) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
}

function entrarEmContato(pacoteName) {
  const mensagem = `Olá! Gostaria de mais informações sobre o pacote: ${pacoteName}`;
  const whatsappLink = `https://wa.me/5511999999999?text=${encodeURIComponent(mensagem)}`;
  window.open(whatsappLink, '_blank');
}

function atualizarTotalBoleto(id) {
  const input = document.getElementById(`dias-${id}`);
  if (!input) return;
  const dias = Math.max(1, parseInt(input.value) || 1);
  const diaria = parseFloat(input.dataset.diaria) || 0;
  const totalEl = document.getElementById(`total-${id}`);
  if (totalEl) {
    totalEl.textContent = 'Total: ' + formatCurrency(diaria * dias);
  }
}

function bindTotalInputs() {
  document.querySelectorAll('input[id^="dias-"]').forEach(input => {
    const id = input.id.replace('dias-', '');
    input.addEventListener('input', () => atualizarTotalBoleto(id));
    atualizarTotalBoleto(id);
  });
}

bindTotalInputs();

async function limparHistorico() {
  const ok = await lumConfirm(
    'Apagar todo o seu histórico de aluguéis? Esta ação não pode ser desfeita.',
    { title: 'Apagar histórico', confirmText: 'Apagar', danger: true }
  );
  if (!ok) return;
  const res = await apiPost({action:'limparHistorico'});
  if (res.success) {
    showToast('Histórico apagado.', 'success');
    setTimeout(() => location.reload(), 900);
  } else {
    showToast(res.error, 'error');
  }
}

async function devolverItem(id) {
  const ok = await lumConfirm(
    'Confirmar devolução deste item?',
    {
      title: 'Confirmar devolução',
      confirmText: 'Devolver',
      details: [
        { label: 'Após devolver', value: 'O item volta ao catálogo' },
        { label: 'Termo de responsabilidade', value: 'O cliente deve devolver o produto em bom estado.' }
      ],
    }
  );
  if (!ok) return;
  const res = await apiPost({action:'devolver', id});
  if (res.success) {
    showToast('Devolução realizada!', 'success');
    setTimeout(() => location.reload(), 1200);
  } else {
    showToast(res.error, 'error');
  }
}
</script>
