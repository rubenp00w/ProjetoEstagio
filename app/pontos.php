<?php
$reuniaoId = (int)($_GET['reuniao'] ?? 0);

$res = consulta(
    'SELECT r.*, o.nome AS orgao, o.tipo AS orgao_tipo
     FROM reunioes r JOIN orgaos o ON o.id = r.orgao_id WHERE r.id = ?',
    [$reuniaoId]
);
$reuniao = $res[0] ?? null;

if (!$reuniao) {
    echo '<p>Reunião não encontrada. <a href="index.php?p=reunioes">Voltar</a></p>';
    return;
}

$eAssembleia = $reuniao['orgao_tipo'] === 'ASSEMBLEIA_FREGUESIA';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'adicionar') {
        $tipo = ($eAssembleia && $_POST['tipo'] === 'ANTES_ORDEM_DIA')
            ? 'ANTES_ORDEM_DIA' : 'ORDEM_DIA';

        $n = consulta(
            'SELECT COALESCE(MAX(ordem_numero), 0) + 1 AS n
             FROM pontos_ordem_dia WHERE reuniao_id = ? AND tipo = ?',
            [$reuniaoId, $tipo]
        )[0]['n'];

        executa(
            'INSERT INTO pontos_ordem_dia (reuniao_id, ordem_numero, tipo, titulo, descricao)
             VALUES (?, ?, ?, ?, ?)',
            [$reuniaoId, (int)$n, $tipo, trim($_POST['titulo']), trim($_POST['descricao'])]
        );
    } elseif ($acao === 'apagar') {
        executa('DELETE FROM pontos_ordem_dia WHERE id = ? AND reuniao_id = ?',
                [(int)$_POST['id'], $reuniaoId]);
    }
    header('Location: index.php?p=pontos&reuniao=' . $reuniaoId);
    exit;
}

$pontos = ['ANTES_ORDEM_DIA' => [], 'ORDEM_DIA' => []];
foreach (consulta(
    'SELECT * FROM pontos_ordem_dia WHERE reuniao_id = ? ORDER BY tipo, ordem_numero',
    [$reuniaoId]
) as $p) {
    $pontos[$p['tipo']][] = $p;
}
$titulos = ['ANTES_ORDEM_DIA' => 'Antes da Ordem do Dia', 'ORDEM_DIA' => 'Ordem do Dia'];
?>
<p><a href="index.php?p=reunioes">&larr; Reuniões</a></p>
<h1><?= e($reuniao['titulo']) ?></h1>
<p><?= e($reuniao['orgao']) ?> &middot; <?= e(date('d/m/Y H:i', strtotime($reuniao['data_hora']))) ?></p>

<h2>Novo ponto</h2>
<form method="post">
  <input type="hidden" name="acao" value="adicionar">
  <select name="tipo">
    <?php if ($eAssembleia): ?>
      <option value="ANTES_ORDEM_DIA">Antes da Ordem do Dia</option>
    <?php endif; ?>
    <option value="ORDEM_DIA">Ordem do Dia</option>
  </select>
  <input name="titulo" placeholder="Título do ponto" required>
  <textarea name="descricao" placeholder="Descrição"></textarea>
  <button>Adicionar</button>
</form>

<?php foreach ($titulos as $tipo => $titulo): ?>
  <?php if ($tipo === 'ANTES_ORDEM_DIA' && !$eAssembleia && !$pontos[$tipo]) continue; ?>
  <h2><?= e($titulo) ?></h2>
  <?php if (!$pontos[$tipo]): ?>
    <p>Sem pontos.</p>
  <?php endif; ?>
  <ol>
    <?php foreach ($pontos[$tipo] as $p): ?>
      <li>
        <strong><?= e($p['titulo']) ?></strong>
        <?php if ($p['descricao']): ?><br><?= nl2br(e($p['descricao'])) ?><?php endif; ?>
        <form method="post" style="display:inline">
          <input type="hidden" name="acao" value="apagar">
          <input type="hidden" name="id" value="<?= $p['id'] ?>">
          <button>Apagar</button>
        </form>
      </li>
    <?php endforeach; ?>
  </ol>
<?php endforeach; ?>