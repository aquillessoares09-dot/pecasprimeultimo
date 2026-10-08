</main>
<footer>
  <div class="container footer-inner">
    <div>
      <img src="<?= $BASE ?>assets/img/logo.jpg" alt="Peças Prime" class="footer-logo-img"><div class="footer-logo">Peças <b>Prime</b></div>
      <p class="muted">Marketplace de autopeças. Tudo para o seu carro, com preço de fábrica.</p>
    </div>
    <div>
      <h4>Institucional</h4>
      <a href="<?= $BASE ?>index.php">Início</a>
      <a href="<?= $BASE ?>products.php">Catálogo</a>
      <a href="<?= $BASE ?>register.php">Cadastre-se</a>
    </div>
    <div>
      <h4>Categorias</h4>
      <?php foreach ($pdo->query('SELECT id, name FROM categories ORDER BY id LIMIT 6') as $cat): ?>
        <a href="<?= $BASE ?>products.php?category=<?= $cat['id'] ?>"><?= e($cat['name']) ?></a>
      <?php endforeach; ?>
    </div>
    <div>
      <h4>Contato</h4>
      <p class="muted">aquillessoares09@gmail.com<br>(21) 96584-2955</p>
    </div>
  </div>
  <div class="footer-bottom">© <?= date('Y') ?> Peças Prime — Todos os direitos reservados.</div>
</footer>
</body>
</html>
