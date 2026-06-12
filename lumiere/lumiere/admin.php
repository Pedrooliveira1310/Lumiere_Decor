<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/template.php';

use Lumiere\Auth\Session;
use Lumiere\Models\Categoria;
use Lumiere\Models\Item;
use Lumiere\Models\Pacote;

Session::requireLogin();
if (!Session::isAdmin()) {
    header('Location: index.php');
    exit;
}

$tipos = Item::getTipos();
$items = Item::all();
$categorias = Categoria::all();
$pacotes = Pacote::all();
$alugados = array_filter($items, fn($i) => $i['status'] === 'alugado');
$historico = array_filter($items, fn($i) => $i['status'] === 'disponivel' && $i['cliente'] !== null);

render_header('admin');
?>
<div class="lum-page">

  <div class="mb-4">
    <h1 class="lum-h1">Painel de Administração</h1>
    <p style="font-size:.82rem;color:var(--secondary-d);margin-top:.3rem">
      Gerencie o inventário e as locações com precisão e elegância.
    </p>
  </div>

  <div class="row g-4 mb-5">
    <!-- ADICIONAR ITEM -->
    <div class="col-12 col-lg-6">
      <div class="lum-card h-100">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div style="width:32px;height:32px;background:var(--primary-lt);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--primary)">
            <i class="bi bi-plus-lg"></i>
          </div>
          <h2 style="font-family:'Playfair Display',serif;font-size:1.2rem;margin:0">Adicionar Novo Item</h2>
        </div>
        <div class="row g-3">
          <div class="col-12">
            <label class="lum-label-field">Modelo</label>
            <input type="text" id="add-modelo" class="lum-input" placeholder="Ex: Cadeira Dior">
          </div>
          <div class="col-6">
            <label class="lum-label-field">Tipo</label>
            <select id="add-tipo" class="lum-input">
              <option value="">Selecione o tipo</option>
              <?php foreach ($tipos as $key => $label): ?>
              <option value="<?= $key ?>"><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="lum-label-field">Detalhes</label>
            <textarea id="add-detalhes" class="lum-input" rows="2"
                      placeholder="Descrição detalhada, material, cor..."></textarea>
          </div>
          <div class="col-12">
            <label class="lum-label-field">Imagem do item</label>
            <input type="file" id="add-imagem" class="lum-input" accept="image/*">
          </div>
          <div class="col-12 d-flex gap-2">
            <button class="btn-lum btn-lum-dark" onclick="adicionarItem()">
              <i class="bi bi-plus-circle"></i> Cadastrar Item
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- CRIAR CATEGORIA -->
    <div class="col-12 col-lg-6">
      <div class="lum-card h-100">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div style="width:32px;height:32px;background:var(--primary-lt);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--primary)">
            <i class="bi bi-tags"></i>
          </div>
          <h2 style="font-family:'Playfair Display',serif;font-size:1.2rem;margin:0">Criar Categoria</h2>
        </div>
        <p style="font-size:.78rem;color:var(--secondary-d);margin-bottom:1rem">Adicione novas categorias de itens com diária.</p>
        <div class="row g-3">
          <div class="col-6">
            <label class="lum-label-field">ID (slug)</label>
            <input id="cat-id" class="lum-input" placeholder="ex: puff-green">
          </div>
          <div class="col-6">
            <label class="lum-label-field">Nome</label>
            <input id="cat-nome" class="lum-input" placeholder="ex: Puff Temático">
          </div>
          <div class="col-6">
            <label class="lum-label-field">Diária (R$)</label>
            <input id="cat-diaria" type="number" min="0" step="0.01" class="lum-input" value="0">
          </div>
          <div class="col-12 d-flex gap-2">
            <button class="btn-lum btn-lum-dark" onclick="criarCategoria()">
              <i class="bi bi-plus-circle"></i> Criar Categoria
            </button>
          </div>
        </div>
        <?php if (!empty($categorias)): ?>
        <hr style="border-color:var(--border);margin:1.5rem 0 1rem">
        <div class="lum-label mb-2">Categorias cadastradas</div>
        <div class="d-flex flex-column gap-2" id="cats-list">
          <?php foreach ($categorias as $cat): ?>
          <div class="d-flex align-items-center justify-content-between gap-2 p-2"
               style="background:var(--bg);border:1px solid var(--border);border-radius:2px"
               id="cat-row-<?= htmlspecialchars($cat['id']) ?>">
            <div>
              <span style="font-weight:600;font-size:.88rem"><?= htmlspecialchars($cat['nome']) ?></span>
              <span style="font-size:.72rem;color:var(--secondary-d);margin-left:.5rem">
                (<?= htmlspecialchars($cat['id']) ?>) — R$ <?= number_format($cat['diaria'], 2, ',', '.') ?>/dia
              </span>
            </div>
            <button class="btn-lum btn-lum-danger btn-lum-sm"
                    onclick="deletarCategoria('<?= htmlspecialchars($cat['id'], ENT_QUOTES) ?>')">
              <i class="bi bi-trash3"></i>
            </button>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-5">
    <!-- CRIAR PACOTE -->
    <div class="col-12">
      <div class="lum-card h-100">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div style="width:32px;height:32px;background:var(--primary-lt);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--primary)">
            <i class="bi bi-box-seam"></i>
          </div>
          <h2 style="font-family:'Playfair Display',serif;font-size:1.2rem;margin:0">Criar Pacote</h2>
        </div>
        <p style="font-size:.78rem;color:var(--secondary-d);margin-bottom:1rem">Cadastre novos pacotes esportivos para a loja do cliente.</p>
        <div class="row g-3">
          <div class="col-6 col-md-3">
            <label class="lum-label-field">ID (slug)</label>
            <input id="pacote-id" class="lum-input" placeholder="ex: fan-fest-80">
          </div>
          <div class="col-6 col-md-3">
            <label class="lum-label-field">Nome</label>
            <input id="pacote-nome" class="lum-input" placeholder="ex: Fan Fest 80 Pessoas">
          </div>
          <div class="col-6 col-md-3">
            <label class="lum-label-field">Capacidade</label>
            <input id="pacote-capacidade" class="lum-input" placeholder="ex: 80 Pessoas">
          </div>
          <div class="col-6 col-md-3">
            <label class="lum-label-field">Valor (R$)</label>
            <input id="pacote-valor" type="number" min="0" step="0.01" class="lum-input" value="0">
          </div>
          <div class="col-12 col-md-6">
            <label class="lum-label-field">Imagem do pacote</label>
            <input id="pacote-imagem" type="file" class="lum-input" accept="image/*">
          </div>
          <div class="col-12 d-flex gap-2">
            <button class="btn-lum btn-lum-dark" onclick="criarPacote()">
              <i class="bi bi-plus-circle"></i> Criar Pacote
            </button>
          </div>
        </div>
        <?php if (!empty($pacotes)): ?>
        <hr style="border-color:var(--border);margin:1.5rem 0 1rem">
        <div class="lum-label mb-2">Pacotes cadastrados</div>
        <div class="d-flex flex-column gap-2">
          <?php foreach ($pacotes as $p): ?>
          <div class="d-flex align-items-center justify-content-between gap-2 p-2"
               style="background:var(--bg);border:1px solid var(--border);border-radius:2px">
            <div>
              <span style="font-weight:600;font-size:.88rem"><?= htmlspecialchars($p['nome']) ?></span>
              <span style="font-size:.72rem;color:var(--secondary-d);margin-left:.5rem">
                (<?= htmlspecialchars($p['id']) ?>) — <?= htmlspecialchars($p['capacidade']) ?> — R$ <?= number_format($p['valor'], 2, ',', '.') ?>
              </span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ALUGUÉIS ATIVOS -->
  <div class="mb-2 d-flex align-items-baseline justify-content-between">
    <h2 class="lum-h2" style="font-family:'Playfair Display',serif">Supervisão ativa de aluguéis</h2>
    <span class="lum-label"><?= count($alugados) ?> ativos</span>
  </div>
  <p style="font-size:.78rem;color:var(--secondary-d);margin-bottom:1.2rem">
    Monitore os itens que estão atualmente em uso pelos clientes.
  </p>

  <?php if (empty($alugados)): ?>
  <div class="lum-card text-center py-4" style="color:var(--secondary-d)">
    <i class="bi bi-inbox" style="font-size:2rem"></i>
    <p style="margin-top:.5rem;font-size:.85rem">Nenhum item alugado no momento.</p>
  </div>
  <?php else: ?>
  <div class="row g-3 lum-cards-row mb-5" id="alugados-grid">
    <?php foreach ($alugados as $item): ?>
    <div class="col-12 col-sm-6 col-md-4" id="alugado-<?= $item['id'] ?>">
      <div class="lum-card lum-product-card">
        <?php $img = Item::getImageUrl($item); ?>
        <div class="lum-card-media">
          <?php if ($img): ?>
          <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item['modelo']) ?>">
          <?php else: ?>
          <i class="bi bi-image" style="font-size:2rem;color:var(--secondary-d)"></i>
          <?php endif; ?>
          <span class="badge-status badge-alugado" style="position:absolute;top:.7rem;left:.7rem">
            <i class="bi bi-clock me-1"></i>Alugado
          </span>
        </div>
        <div class="lum-card-body">
          <div class="lum-card-title"><?= htmlspecialchars($item['modelo']) ?></div>
          <div class="lum-card-meta">Cliente: <?= htmlspecialchars($item['cliente'] ?? '') ?></div>
          <div class="d-flex justify-content-between align-items-center lum-card-meta" style="margin-bottom:.75rem">
            <span>Retorna em</span>
            <span style="font-weight:600;color:var(--primary)">
              <?php
              $retorno = date('d M Y', strtotime($item['data_aluguel'] . ' + ' . $item['dias'] . ' days'));
              echo $retorno;
              ?>
            </span>
          </div>
          <div class="lum-card-actions">
            <button class="btn-lum btn-lum-success btn-lum-sm w-100"
                    onclick="devolverItem(<?= $item['id'] ?>)">
              <i class="bi bi-arrow-return-left"></i> Devolução
            </button>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- INVENTÁRIO COMPLETO -->
  <div class="mb-2 d-flex align-items-baseline justify-content-between">
    <h2 class="lum-h2" style="font-family:'Playfair Display',serif">Inventário</h2>
    <span class="lum-label"><?= count($items) ?> itens</span>
  </div>
  <p style="font-size:.78rem;color:var(--secondary-d);margin-bottom:1.2rem">Controle total sobre o acervo.</p>

  <div class="lum-card p-0 mb-5">
    <div class="lum-table-wrap">
      <table class="lum-table">
        <thead>
          <tr>
            <th>Modelo</th>
            <th>Tipo</th>
            <th>Detalhes</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody id="inv-tbody">
          <?php foreach ($items as $item): ?>
          <tr id="inv-<?= $item['id'] ?>">
            <td style="font-weight:500"><?= htmlspecialchars($item['modelo']) ?></td>
            <td><?= htmlspecialchars($tipos[$item['tipo']] ?? $item['tipo']) ?></td>
            <td style="font-size:.8rem;color:var(--secondary-d);max-width:200px">
              <?= htmlspecialchars(mb_strimwidth($item['detalhes'], 0, 55, '…')) ?>
            </td>
            <td>
              <?php if ($item['status'] === 'disponivel'): ?>
              <span class="badge-status badge-disponivel">Disponível</span>
              <?php else: ?>
              <span class="badge-status badge-alugado">Alugado (<?= $item['dias'] ?> dias restantes)</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="d-flex gap-2 align-items-center flex-wrap">
                <?php if ($item['status'] === 'disponivel'): ?>

                <?php else: ?>
                <button class="btn-lum btn-lum-success btn-lum-sm"
                        onclick="devolverItem(<?= $item['id'] ?>)">
                  <i class="bi bi-arrow-return-left"></i> Devolver
                </button>
                <?php endif; ?>
                <button class="btn-lum btn-lum-danger btn-lum-sm"
                        onclick="deletarItem(<?= $item['id'] ?>)">
                  <i class="bi bi-trash3"></i>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php render_footer(); ?>

<script>
async function criarCategoria() {
  const id = document.getElementById('cat-id').value.trim();
  const nome = document.getElementById('cat-nome').value.trim();
  const diaria = parseFloat(document.getElementById('cat-diaria').value) || 0;
  if (!id || !nome) { showToast('Preencha id e nome.', 'error'); return; }
  const res = await apiPost({action:'adicionarCategoria', id, nome, diaria});
  if (res.success) {
    showToast('Categoria criada.', 'success');
    setTimeout(() => location.reload(), 900);
  } else {
    showToast(res.error, 'error');
  }
}

async function criarPacote() {
  const id = document.getElementById('pacote-id').value.trim();
  const nome = document.getElementById('pacote-nome').value.trim();
  const capacidade = document.getElementById('pacote-capacidade').value.trim();
  const valor = parseFloat(document.getElementById('pacote-valor').value) || 0;
  const imagem = document.getElementById('pacote-imagem').files[0];
  if (!id || !nome || !capacidade || valor <= 0) { showToast('Preencha todos os campos do pacote.', 'error'); return; }
  const payload = { action:'adicionarPacote', id, nome, capacidade, valor };
  if (imagem) payload.imagem = imagem;
  const res = await apiPost(payload);
  if (res.success) {
    showToast('Pacote criado com sucesso.', 'success');
    setTimeout(() => location.reload(), 900);
  } else {
    showToast(res.error, 'error');
  }
}

async function deletarCategoria(id) {
  const ok = await lumConfirm(
    'Só é possível excluir se não houver itens usando esta categoria.',
    { title: 'Excluir categoria', message: 'Deseja excluir esta categoria?', confirmText: 'Excluir', danger: true }
  );
  if (!ok) return;
  const res = await apiPost({action:'deletarCategoria', id});
  if (res.success) {
    showToast('Categoria excluída.', 'success');
    const row = document.getElementById('cat-row-' + id);
    if (row) row.remove();
    setTimeout(() => location.reload(), 900);
  } else {
    showToast(res.error, 'error');
  }
}

async function adicionarItem() {
  const modelo   = document.getElementById('add-modelo').value.trim();
  const tipo     = document.getElementById('add-tipo').value;
  const detalhes = document.getElementById('add-detalhes').value.trim();
  const imagem   = document.getElementById('add-imagem').files[0];
  if (!modelo || !tipo || !detalhes) { showToast('Preencha todos os campos.', 'error'); return; }
  const payload = {action:'adicionar', modelo, tipo, detalhes};
  if (imagem) payload.imagem = imagem;
  const res = await apiPost(payload);
  if (res.success) {
    showToast('Item cadastrado com sucesso!', 'success');
    setTimeout(() => location.reload(), 1200);
  } else {
    showToast(res.error, 'error');
  }
}

async function devolverItem(id) {
  const res = await apiPost({action:'devolver', id});
  if (res.success) {
    showToast('Item devolvido com sucesso!', 'success');
    setTimeout(() => location.reload(), 1200);
  } else {
    showToast(res.error, 'error');
  }
}

async function deletarItem(id) {
  const ok = await lumConfirm(
    'O item será removido do inventário e não poderá ser recuperado.',
    { title: 'Remover item', message: 'Remover este item permanentemente?', confirmText: 'Remover', danger: true }
  );
  if (!ok) return;
  const res = await apiPost({action:'deletar', id});
  if (res.success) {
    showToast('Item removido.', 'success');
    const rows = [`row-${id}`,`inv-${id}`,`alugado-${id}`];
    rows.forEach(r => { const el = document.getElementById(r); if(el) el.remove(); });
  } else {
    showToast(res.error, 'error');
  }
}
</script>
