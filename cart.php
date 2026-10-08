<?php
require_once __DIR__ . '/config.php';

// "Comprar agora" direto da página do produto
if (isset($_GET['buy'])) {
    $pid = (int)$_GET['buy'];
    $st = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
    $st->execute([$pid]);
    if ($p = $st->fetch()) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $_SESSION['cart'][$pid] = min(($_SESSION['cart'][$pid] ?? 0) + 1, $p['stock']);
    }
    header('Location: cart.php');
    exit;
}

// Atualizações do carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)($_POST['product_id'] ?? 0);
    if (isset($_POST['update'])) {
        $qty = (int)$_POST['qty'];
        if ($qty <= 0) unset($_SESSION['cart'][$pid]); else $_SESSION['cart'][$pid] = $qty;
    }
    if (isset($_POST['remove'])) unset($_SESSION['cart'][$pid]);
    header('Location: cart.php');
    exit;
}

$pageTitle = 'Carrinho';
include __DIR__ . '/includes/header.php';

$items = [];
foreach ($_SESSION['cart'] ?? [] as $pid => $qty) {
    $st = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $st->execute([$pid]);
    if ($p = $st->fetch()) { $p['qty'] = $qty; $items[] = $p; }
}
$subtotal = 0;
foreach ($items as $i) $subtotal += $i['price'] * $i['qty'];
$total = $subtotal;
$shipping = ($subtotal >= 199 || $subtotal == 0) ? 0 : 29.90;
$total += $shipping;
?>

<h1 class="page-title">Seu carrinho (<?= count($items) ?> itens)</h1>

<?php if (!$items): ?>
  <p class="empty">Seu carrinho está vazio. <a href="products.php">Ver produtos</a></p>
<?php else: ?>
<div class="cart-layout">
  <div class="cart-items">
    <?php foreach ($items as $p): ?>
      <div class="cart-row">
        <img src="<?= $BASE . product_img($p) ?>" alt="<?= e($p['name']) ?>">
        <div class="cart-info">
          <a href="product.php?id=<?= $p['id'] ?>"><h3><?= e($p['name']) ?></h3></a>
          <p class="cat"><?= e($p['brand']) ?></p>
          <p class="price"><?= money($p['price']) ?></p>
        </div>
        <div class="cart-controls">
          <form method="post" class="inline" novalidate>
            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
            <?php $ok = $p['stock'] > 0 && $p['qty'] <= $p['stock']; ?>
            <input type="number" name="qty" value="<?= $p['qty'] ?>" data-stock="<?= (int)$p['stock'] ?>" class="qty-input <?= $ok ? 'qty-ok' : 'qty-danger' ?>" onchange="this.form.querySelector('button[name=update]').click()">
            <span class="stock-note <?= $ok ? 'ok' : 'danger' ?>">
              <?= $p['stock'] <= 0 ? 'Sem estoque' : ($p['qty'] > $p['stock'] ? 'Só ' . $p['stock'] . ' em estoque' : $p['stock'] . ' disponíveis') ?>
            </span>
            <button class="link-btn" type="submit" name="update" value="1">Atualizar</button>
            <button class="link-btn danger" type="submit" name="remove" value="1">Excluir</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <aside class="cart-summary">
    <h3>Resumo</h3>
    <p>Subtotal: <b><?= money($subtotal) ?></b></p>
    <p>Frete: <b><?= $shipping ? money($shipping) : 'Grátis' ?></b></p>
    <?php if ($shipping): ?><p class="muted">Frete grátis acima de R$ 199!</p><?php endif; ?>
    <hr>
    <p class="total">Total: <b><?= money($total) ?></b></p>
    <p class="error" id="checkout-msg" style="display:none">Há itens com quantidade acima do estoque (em vermelho). Ajuste ou remova para finalizar a compra.</p>
    <a class="btn wide" id="checkout-btn" href="checkout.php">Finalizar compra</a>
    <a class="muted" href="products.php">Continuar comprando</a>
  </aside>
</div>
<?php endif; ?>
<script>
var qtyInputs = document.querySelectorAll('.qty-input[data-stock]');
var checkoutBtn = document.getElementById('checkout-btn');
var checkoutMsg = document.getElementById('checkout-msg');

// Bloqueia "Finalizar compra" enquanto algum item estiver acima do estoque
function checkAll() {
  var bad = false;
  qtyInputs.forEach(function(inp) {
    var q = parseInt(inp.value, 10) || 0;
    var s = parseInt(inp.dataset.stock, 10) || 0;
    if (s <= 0 || q <= 0 || q > s) bad = true;
  });
  if (checkoutBtn) {
    checkoutBtn.dataset.blocked = bad ? '1' : '0';
    checkoutBtn.classList.toggle('checkout-blocked', bad);
  }
  if (checkoutMsg && !bad) checkoutMsg.style.display = 'none';
}
if (checkoutBtn) {
  checkoutBtn.addEventListener('click', function(e) {
    if (this.dataset.blocked === '1') {
      e.preventDefault();
      if (checkoutMsg) checkoutMsg.style.display = 'block';
    }
  });
}

qtyInputs.forEach(function(inp) {
  var note = inp.closest('form').querySelector('.stock-note');
  function upd() {
    var q = parseInt(inp.value, 10) || 0;
    var s = parseInt(inp.dataset.stock, 10) || 0;
    var ok = q > 0 && q <= s;
    inp.classList.toggle('qty-ok', ok);
    inp.classList.toggle('qty-danger', !ok);
    if (note) {
      note.className = 'stock-note ' + (ok ? 'ok' : 'danger');
      note.textContent = s <= 0 ? 'Sem estoque' : (q > s ? 'Só ' + s + ' em estoque' : s + ' disponíveis');
    }
    checkAll();
  }
  inp.addEventListener('input', upd);
  upd();
});
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
