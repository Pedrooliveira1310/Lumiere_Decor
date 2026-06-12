<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/template.php';

use Lumiere\Auth\Session;
use Lumiere\Models\Historico;
use Lumiere\Models\Item;
use Lumiere\Models\Pacote;

Session::requireLogin();
if (!Session::isAdmin()) {
    header('Location: index.php');
    exit;
}

$historico = Historico::getAll();
$summary   = Historico::getFinanceSummary();
$alugados  = array_filter(Item::all(), fn($i) => $i['status'] === 'alugado');
$pacotes   = Pacote::all();

$ganhosEmAndamento = 0.0;
foreach ($alugados as $item) {
    $ganhosEmAndamento += Item::getDiariaFromItem($item) * max(1, (int)($item['dias'] ?? 1));
}

$ganhosTotais = $summary['ganhos_realizados'] + $ganhosEmAndamento;

render_header('financeiro');
?>
<div class="lum-page">

  <div class="mb-4">
    <h1 class="lum-h1">Ganhos & Devoluções</h1>
    <p style="font-size:.82rem;color:var(--secondary-d);margin-top:.3rem">
      Acompanhe receitas, aluguéis ativos e devoluções concluídas.
    </p>
  </div>

  <div class="row g-3 mb-5">
    <div class="col-12 col-md-4">
      <div class="lum-card text-center h-100">
        <div class="lum-label mb-2">Ganhos totais (realizados + em andamento)</div>
        <div style="font-family:'Playfair Display',serif;font-size:1.8rem;color:var(--primary);font-weight:700">
          R$ <?= number_format($ganhosTotais, 2, ',', '.') ?>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="lum-card text-center h-100">
        <div class="lum-label mb-2">Em andamento</div>
        <div style="font-family:'Playfair Display',serif;font-size:1.8rem;color:var(--dark);font-weight:700">
          R$ <?= number_format($ganhosEmAndamento, 2, ',', '.') ?>
        </div>
        <div style="font-size:.75rem;color:var(--secondary-d);margin-top:.4rem"><?= count($alugados) ?> aluguéis ativos</div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="lum-card text-center h-100">
        <div class="lum-label mb-2">Devoluções concluídas</div>
        <div style="font-family:'Playfair Display',serif;font-size:1.8rem;color:var(--success-d);font-weight:700">
          <?= $summary['devolucoes'] ?>
        </div>
        <div style="font-size:.75rem;color:var(--secondary-d);margin-top:.4rem">
          R$ <?= number_format($summary['ganhos_realizados'], 2, ',', '.') ?> já recebidos
        </div>
      </div>
    </div>
  </div>

  <!-- ALUGUÉIS ATIVOS -->
  <div class="mb-2 d-flex align-items-baseline justify-content-between">
    <h2 class="lum-h2" style="font-family:'Playfair Display',serif">Aluguéis ativos</h2>
    <span class="lum-label"><?= count($alugados) ?> itens</span>
  </div>
  <?php if (empty($alugados)): ?>
  <div class="lum-card text-center py-4 mb-5" style="color:var(--secondary-d)">
    <i class="bi bi-inbox" style="font-size:2rem"></i>
    <p style="margin-top:.5rem;font-size:.85rem">Nenhum aluguel ativo no momento.</p>
  </div>
  <?php else: ?>
  <div class="lum-card p-0 mb-5">
    <div class="lum-table-wrap">
      <table class="lum-table">
        <thead>
          <tr>
            <th>Item</th>
            <th>Cliente</th>
            <th>Dias</th>
            <th>Valor estimado</th>
            <th>Retorno previsto</th>
            <th>Ação</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alugados as $item):
            $valor = Item::getDiariaFromItem($item) * max(1, (int)($item['dias'] ?? 1));
            $retorno = date('d/m/Y', strtotime(($item['data_aluguel'] ?? 'today') . ' + ' . (int)($item['dias'] ?? 1) . ' days'));
          ?>
          <tr id="fin-<?= $item['id'] ?>">
            <td style="font-weight:500"><?= htmlspecialchars($item['modelo']) ?></td>
            <td><?= htmlspecialchars($item['cliente'] ?? '—') ?></td>
            <td><?= (int)($item['dias'] ?? 0) ?></td>
            <td>R$ <?= number_format($valor, 2, ',', '.') ?></td>
            <td><?= $retorno ?></td>
            <td>
              <button class="btn-lum btn-lum-success btn-lum-sm" onclick="devolverItem(<?= $item['id'] ?>)">
                <i class="bi bi-arrow-return-left"></i> Registrar devolução
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- HISTÓRICO -->
  <div class="mb-2 d-flex align-items-baseline justify-content-between flex-wrap gap-2">
    <h2 class="lum-h2" style="font-family:'Playfair Display',serif">Histórico de transações</h2>
    <div class="d-flex align-items-center gap-2">
      <span class="lum-label"><?= count($historico) ?> registros</span>
      <?php if (!empty($historico)): ?>
      <button class="btn-lum btn-lum-danger btn-lum-sm" onclick="limparHistorico()">
        <i class="bi bi-trash3"></i> Apagar todo o histórico
      </button>
      <?php endif; ?>
    </div>
  </div>
  <div class="lum-card p-0 mb-5">
    <?php if (empty($historico)): ?>
    <div class="p-4 text-center" style="color:var(--secondary-d);font-size:.88rem">Nenhuma transação registrada.</div>
    <?php else: ?>
    <div class="lum-table-wrap">
      <table class="lum-table">
        <thead>
          <tr>
            <th>Cliente</th>
            <th>Item</th>
            <th>Dias</th>
            <th>Valor</th>
            <th>Alugado em</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (array_reverse($historico) as $entry): ?>
          <tr>
            <td><?= htmlspecialchars($entry['username'] ?? '—') ?></td>
            <td style="font-weight:500"><?= htmlspecialchars($entry['modelo'] ?? '—') ?></td>
            <td><?= (int)($entry['dias'] ?? 0) ?></td>
            <td>R$ <?= number_format((float)($entry['valor_total'] ?? 0), 2, ',', '.') ?></td>
            <td style="font-size:.8rem;color:var(--secondary-d)">
              <?= !empty($entry['rented_at']) ? date('d/m/Y H:i', strtotime($entry['rented_at'])) : '—' ?>
            </td>
            <td>
              <?php if (!empty($entry['returned_at'])): ?>
              <span class="badge-status badge-disponivel">Devolvido</span>
              <?php else: ?>
              <span class="badge-status badge-alugado">Em uso</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- VALORES DOS PACOTES -->
  <div class="mb-2">
    <h2 class="lum-h2" style="font-family:'Playfair Display',serif">Valores dos pacotes esportivos</h2>
    <p style="font-size:.78rem;color:var(--secondary-d);margin-bottom:1.2rem">
      Defina os preços dos pacotes disponíveis para os clientes.
    </p>
  </div>
  <div class="lum-card">
    <div class="row g-3">
      <?php foreach ($pacotes as $p): ?>
      <div class="col-12 pacote-row" data-id="<?= $p['id'] ?>">
        <div style="display:flex;gap:.8rem;align-items:center;flex-wrap:wrap">
          <div style="flex:1;min-width:180px">
            <div style="font-weight:600;font-size:.9rem;color:var(--primary)"><?= htmlspecialchars($p['nome']) ?></div>
            <div style="font-size:.7rem;color:var(--secondary-d)"><?= htmlspecialchars($p['capacidade']) ?></div>
          </div>
          <div style="display:flex;gap:.4rem;align-items:center">
            <span style="font-size:.8rem;color:var(--secondary-d)">R$</span>
            <input type="number" class="lum-input pacote-valor" data-id="<?= $p['id'] ?>"
                   value="<?= $p['valor'] ?>" min="0" step="100"
                   style="width:100px;padding:.4rem;font-size:.85rem;text-align:right">
            <button class="btn-lum btn-lum-gold btn-lum-sm" onclick="salvarPacote('<?= $p['id'] ?>', this)">
              <i class="bi bi-check"></i>
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<?php render_footer(); ?>

<script>
async function limparHistorico() {
  const ok = await lumConfirm(
    'Apagar TODO o histórico de transações? Esta ação não pode ser desfeita.',
    { title: 'Apagar histórico', confirmText: 'Apagar tudo', danger: true }
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
  const res = await apiPost({action:'devolver', id});
  if (res.success) {
    showToast('Devolução registrada!', 'success');
    setTimeout(() => location.reload(), 1000);
  } else {
    showToast(res.error, 'error');
  }
}

async function salvarPacote(id, btn) {
  const valor = document.querySelector(`input.pacote-valor[data-id="${id}"]`).value;
  if (!valor || valor < 0) { showToast('Insira um valor válido.', 'error'); return; }
  const res = await apiPost({action:'atualizarPacote', id, valor});
  if (res.success) {
    showToast('Pacote atualizado!', 'success');
    btn.style.background = 'var(--success)';
    setTimeout(() => { btn.style.background = ''; }, 1500);
  } else {
    showToast(res.error, 'error');
  }
}
</script>
