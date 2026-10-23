<?php
$estados = ['AGENDADA', 'EM_CURSO', 'FINALIZADA', 'CANCELADA'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'agendar') {
        executa(
            'INSERT INTO reunioes (orgao_id, titulo, descricao, data_hora, local_reuniao)
             VALUES (?, ?, ?, ?, ?)',
            [
                (int)$_POST['orgao_id'],
                trim($_POST['titulo']),
                trim($_POST['descricao']),
                str_replace('T', ' ', $_POST['data_hora']) . ':00',
                trim($_POST['local_reuniao']),
            ]
        );
    } elseif ($acao === 'estado') {
        if (in_array($_POST['estado'], $estados, true)) {
            executa('UPDATE reunioes SET estado = ? WHERE id = ?',
                    [$_POST['estado'], (int)$_POST['id']]);
        }
    }
    header('Location: index.php?p=reunioes');
    exit;
}

$orgaos   = consulta('SELECT id, nome FROM orgaos WHERE ativo = 1 ORDER BY nome');
$reunioes = consulta(
    'SELECT r.*, o.nome AS orgao
     FROM reunioes r JOIN orgaos o ON o.id = r.orgao_id
     ORDER BY r.data_hora DESC'
);
?>
<h1>Reuniões</h1>

<h2>Agendar reunião</h2>
<form method="post">
  <input type="hidden" name="acao" value="agendar">
  <select name="orgao_id" required>
    <?php foreach ($orgaos as $o): ?>
      <option value="<?= $o['id'] ?>"><?= e($o['nome']) ?></option>
    <?php endforeach; ?>
  </select>
  <input name="titulo" placeholder="Título" required>
  <textarea name="descricao" placeholder="Descrição"></textarea>
  <input type="datetime-local" name="data_hora" required>
  <input name="local_reuniao" placeholder="Local">
  <button>Agendar</button>
</form>

<h2>Lista</h2>
<table>
  <tr><th>Data</th><th>Órgão</th><th>Título</th><th>Local</th><th>Estado</th><th></th></tr>
  <?php foreach ($reunioes as $r): ?>
    <tr>
      <td><?= e(date('d/m/Y H:i', strtotime($r['data_hora']))) ?></td>
      <td><?= e($r['orgao']) ?></td>
      <td><?= e($r['titulo']) ?></td>
      <td><?= e($r['local_reuniao']) ?></td>
      <td>
        <form method="post">
          <input type="hidden" name="acao" value="estado">
          <input type="hidden" name="id" value="<?= $r['id'] ?>">
          <select name="estado" onchange="this.form.submit()">
            <?php foreach ($estados as $s): ?>
              <option value="<?= $s ?>" <?= $r['estado'] === $s ? 'selected' : '' ?>>
                <?= e(ucfirst(strtolower(str_replace('_', ' ', $s)))) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </form>
      </td>
      <td><a href="index.php?p=pontos&reuniao=<?= $r['id'] ?>">Ordem do dia</a></td>
    </tr>
  <?php endforeach; ?>
</table>