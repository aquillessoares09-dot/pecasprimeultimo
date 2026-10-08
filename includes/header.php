<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Peças Prime') ?> | Peças Prime</title>
<link rel="stylesheet" href="<?= $BASE ?>assets/css/style.css">
</head>
<body>
<header class="topbar">
  <div class="container topbar-inner">
    <a href="<?= $BASE ?>index.php" class="logo">
      <img src="<?= $BASE ?>assets/img/logo.jpg" alt="Peças Prime" class="logo-img">
      <span>Peças <b>Prime</b></span>
    </a>
    <form action="<?= $BASE ?>products.php" method="get" class="search-form">
      <input type="text" name="q" placeholder="Buscar peças, marcas e muito mais..." value="<?= e($_GET['q'] ?? '') ?>">
      <button type="submit">Buscar</button>
    </form>
    <nav class="top-links">
      <?php if (current_user()): ?>
        <?php if (current_user()['is_admin']): ?>
          <a href="<?= $BASE ?>admin/index.php">Painel</a>
        <?php endif; ?>
        <a href="<?= $BASE ?>my-orders.php">Meus pedidos</a>
        <a href="<?= $BASE ?>logout.php" class="muted">Sair (<?= e(explode(' ', current_user()['name'])[0]) ?>)</a>
      <?php else: ?>
        <a href="<?= $BASE ?>login.php">Entrar</a>
        <a href="<?= $BASE ?>register.php">Criar conta</a>
      <?php endif; ?>
      <a href="<?= $BASE ?>cart.php" class="cart-link">Carrinho <span class="badge"><?= cart_count() ?></span></a>
    </nav>
  </div>
  <div class="container cats-bar">
    <?php foreach ($pdo->query('SELECT * FROM categories ORDER BY name') as $cat): ?>
      <a href="<?= $BASE ?>products.php?category=<?= $cat['id'] ?>"><?= e($cat['name']) ?></a>
    <?php endforeach; ?>
  </div>
</header>
<main class="container">
