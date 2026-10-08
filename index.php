<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Home';
include __DIR__ . '/includes/header.php';

// Catálogo completo (todos os produtos)
$products = $pdo->query('SELECT p.*, c.name AS category FROM products p JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC')->fetchAll();
?>

<section class="hero">
  <div class="hero-text">
    <h1>Tudo para o seu carro, <span>com preço de fábrica</span></h1>
    <p>Milhares de peças das melhores marcas. Frete grátis acima de R$ 199.</p>
    <a class="btn" href="products.php">Ver catálogo completo</a>
  </div>
  <div class="hero-badge">Até <b>40% OFF</b><br>em freios e suspensão</div>
</section>

<h2 class="section-title">Catálogo completo <span class="muted">(<?= count($products) ?> produtos)</span></h2>
<?php if (!$products): ?>
  <p class="empty">Nenhum produto cadastrado ainda.</p>
<?php else: ?>
<div class="grid">
  <?php foreach ($products as $p): ?>
    <a class="card" href="product.php?id=<?= $p['id'] ?>">
      <img src="<?= $BASE . product_img($p) ?>" alt="<?= e($p['name']) ?>">
      <?php if ($p['old_price']): ?>
        <span class="discount"><?= round((1 - $p['price'] / $p['old_price']) * 100) ?>% OFF</span>
      <?php endif; ?>
      <div class="card-body">
        <p class="cat"><?= e($p['category']) ?></p>
        <h3><?= e($p['name']) ?></h3>
        <?php if ($p['free_shipping']): ?><p class="freeship">Frete grátis</p><?php endif; ?>
        <p class="price"><?= money($p['price']) ?></p>
      </div>
    </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
