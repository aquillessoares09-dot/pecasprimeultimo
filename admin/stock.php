<?php
require_once __DIR__ . '/../config.php';
if (!current_user() || !current_user()['is_admin']) { http_response_code(403); die('Acesso restrito.'); }

// Cadastrar fornecedor
if (isset($_POST['new_supplier'])) {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $pdo->prepare('INSERT INTO suppliers (name, phone, email) VALUES (?, ?, ?)')
            ->execute([$name, trim($_POST['phone'] ?? ''), trim($_POST['email'] ?? '')]);
    }
    header('Location: stock.php?tab=fornecedores');
    exit;
}

// Registrar entrada de estoque
if (isset($_POST['entry'])) {
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = (int)($_POST['qty'] ?? 0);
    $cost = (float)str_replace(',', '.', (string)($_POST['unit_cost'] ?? 0));
    $sid = (int)($_POST['supplier_id'] ?? 0) ?: null;
    if ($pid > 0 && $qty > 0) {
        $st = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
        $st->execute([$pid]);
        if ($st->fetch()) {
            $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO stock_entries (product_id, supplier_id, qty, unit_cost) VALUES (?, ?, ?, ?)')
                ->execute([$pid, $sid, $qty, $cost]);
            $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?')->execute([$qty, $pid]);
            $pdo->commit();
        }
    }
    header('Location: stock.php');
    exit;
}

$products = $pdo->query('SELECT id, name, stock FROM products ORDER BY name')->fetchAll();
$suppliers = $pdo->query('SELECT * FROM suppliers ORDER BY name')->fetchAll();
$entries = $pdo->query('SELECT se.*, p.name AS product_name, s.name AS supplier_name
                        FROM stock_entries se
                        JOIN products p ON p.id = se.product_id
                        LEFT JOIN suppliers s ON s.id = se.supplier_id
                        ORDER BY se.created_at DESC LIMIT 50')->fetchAll();

$pageTitle = 'Estoque';
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-head">
  <h1 class="page-title">Estoque e fornecedores</h1>
  <a class="btn secondary" href="index.php">← Painel</a>
</div>

<h2 class="section-title">Registrar entrada de estoque</h2>
<form method="post" class="stock-form">
  <label>Produto
    <select name="product_id" required>
      <option value="">Selecione...</option>
      <?php foreach ($products as $p): ?>
        <option value="<?= $p['id'] ?>"><?= e($p['name']) ?> (atual: <?= $p['stock'] ?>)</option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>Fornecedor
    <select name="supplier_id">
      <option value="">Sem fornecedor</option>
      <?php foreach ($suppliers as $s): ?>
        <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>Quantidade recebida<input type="number" name="qty" min="1" required></label>
  <label>Preço de custo unitário (R$)<input type="text" name="unit_cost" placeholder="0,00"></label>
  <button class="btn" type="submit" name="entry" value="1">Dar entrada</button>
</form>
<p class="muted">A quantidade é somada ao estoque atual do produto e o custo fica registrado para consulta.</p>

<h2 class="section-title">Fornecedores</h2>
<form method="post" class="stock-form">
  <label>Nome<input type="text" name="name" required></label>
  <label>Telefone<input type="text" name="phone"></label>
  <label>E-mail<input type="text" name="email"></label>
  <button class="btn secondary" type="submit" name="new_supplier" value="1">Cadastrar fornecedor</button>
</form>
<?php if ($suppliers): ?>
<table class="table">
  <tr><th>Fornecedor</th><th>Telefone</th><th>E-mail</th></tr>
  <?php foreach ($suppliers as $s): ?>
  <tr><td><?= e($s['name']) ?></td><td><?= e($s['phone']) ?></td><td><?= e($s['email']) ?></td></tr>
  <?php endforeach; ?>
</table>
<?php else: ?>
  <p class="empty">Nenhum fornecedor cadastrado ainda.</p>
<?php endif; ?>

<h2 class="section-title">Últimas entradas</h2>
<?php if (!$entries): ?><p class="empty">Nenhuma entrada registrada ainda.</p><?php endif; ?>
<?php foreach ($entries as $en): ?>
<div class="order-card">
  <div class="order-head">
    <b><?= e($en['product_name']) ?></b>
    <span>+<?= $en['qty'] ?> unidades</span>
    <span>Fornecedor: <?= e($en['supplier_name'] ?? '—') ?></span>
    <span>Custo unit.: <?= money($en['unit_cost']) ?> · Total: <?= money($en['unit_cost'] * $en['qty']) ?></span>
    <span><?= date('d/m/Y H:i', strtotime($en['created_at'])) ?></span>
  </div>
</div>
<?php endforeach; ?>

<style>
.stock-form { display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end; background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 16px 18px; margin-bottom: 8px; }
.stock-form label { display: flex; flex-direction: column; gap: 4px; font-size: 13px; font-weight: 600; margin: 0; }
.stock-form select, .stock-form input { min-width: 160px; padding: 8px 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
</style>
<?php include __DIR__ . '/../includes/footer.php'; ?>
