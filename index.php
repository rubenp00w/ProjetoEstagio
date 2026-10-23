<?php
ob_start();
require __DIR__ . '/app/bootstrap.php';

$paginas = ['orgaos', 'reunioes', 'pontos'];
$p = $_GET['p'] ?? 'orgaos';
if (!in_array($p, $paginas, true)) {
    $p = 'orgaos';
}

// A página "pontos" faz parte do separador "Reuniões"
$menuAtivo = ($p === 'pontos') ? 'reunioes' : $p;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestão de Atas</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <nav>
    <span class="marca">Gestão de Atas</span>
    <a href="index.php?p=orgaos" class="<?= $menuAtivo === 'orgaos' ? 'ativo' : '' ?>">Órgãos e Membros</a>
    <a href="index.php?p=reunioes" class="<?= $menuAtivo === 'reunioes' ? 'ativo' : '' ?>">Reuniões</a>
  </nav>
  <main>
    <?php require __DIR__ . "/app/$p.php"; ?>
  </main>
</body>
</html>
<?php ob_end_flush(); ?>