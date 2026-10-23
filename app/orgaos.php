<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'orgao') {
        executa('INSERT INTO orgaos (nome, tipo) VALUES (?, ?)',
                [trim($_POST['nome']), $_POST['tipo']]);
    } elseif ($acao === 'membro') {
        executa('INSERT INTO membros (nome, email, telefone) VALUES (?, ?, ?)',
                [trim($_POST['nome']), trim($_POST['email']), trim($_POST['telefone'])]);
    } elseif ($acao === 'associar') {
        executa(
            'INSERT INTO membro_orgao (membro_id, orgao_id, cargo, direito_voto)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE cargo = VALUES(cargo),
                                     direito_voto = VALUES(direito_voto)',
            [
                (int)$_POST['membro_id'],
                (int)$_POST['orgao_id'],
                trim($_POST['cargo']),
                isset($_POST['direito_voto']) ? 1 : 0,
            ]
        );
    } elseif ($acao === 'remover') {
        executa('DELETE FROM membro_orgao WHERE orgao_id = ? AND membro_id = ?',
                [(int)$_POST['orgao_id'], (int)$_POST['membro_id']]);
    }
    header('Location: index.php?p=orgaos');
    exit;
}

$orgaos  = consulta('SELECT * FROM orgaos WHERE ativo = 1 ORDER BY nome');
$membros = consulta('SELECT * FROM membros WHERE ativo = 1 ORDER BY nome');
$tipos   = [
    'JUNTA_FREGUESIA'      => 'Junta de Freguesia',
    'ASSEMBLEIA_FREGUESIA' => 'Assembleia de Freguesia',
    'COMISSAO_AVALIACAO'   => 'Comissão de Avaliação',
    'CONSELHO_COORDENADOR' => 'Conselho Coordenador de Avaliação',
    'OUTRO'                => 'Outro',
];
?>
<h1>Órgãos e Membros</h1>

<h2>Novo órgão</h2>
<form method="post">
  <input type="hidden" name="acao" value="orgao">
  <input name="nome" placeholder="Nome do órgão" required>
  <select name="tipo">
    <?php foreach ($tipos as $k => $v): ?>
      <option value="<?= e($k) ?>"><?= e($v) ?></option>
    <?php endforeach; ?>
  </select>
  <button>Criar</button>
</form>

<h2>Novo membro</h2>
<form method="post">
  <input type="hidden" name="acao" value="membro">
  <input name="nome" placeholder="Nome" required>
  <input name="email" type="email" placeholder="Email">
  <input name="telefone" placeholder="Telefone">
  <button>Criar</button>
</form>

<h2>Associar membro a órgão</h2>
<form method="post">
  <input type="hidden" name="acao" value="associar">
  <select name="orgao_id" required>
    <?php foreach ($orgaos as $o): ?>
      <option value="<?= $o['id'] ?>"><?= e($o['nome']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="membro_id" required>
    <?php foreach ($membros as $m): ?>
      <option value="<?= $m['id'] ?>"><?= e($m['nome']) ?></option>
    <?php endforeach; ?>
  </select>
  <input name="cargo" placeholder="Cargo (ex: Presidente)">
  <label><input type="checkbox" name="direito_voto" checked> Direito a voto</label>
  <button>Associar</button>
</form>

<h2>Composição dos órgãos</h2>
<?php foreach ($orgaos as $o): ?>
  <h3><?= e($o['nome']) ?> <small>(<?= e($tipos[$o['tipo']] ?? $o['tipo']) ?>)</small></h3>
  <?php
    $lista = consulta(
        'SELECT m.id, m.nome, mo.cargo, mo.direito_voto
         FROM membro_orgao mo JOIN membros m ON m.id = mo.membro_id
         WHERE mo.orgao_id = ? ORDER BY m.nome',
        [$o['id']]
    );
  ?>
  <table>
    <tr><th>Nome</th><th>Cargo</th><th>Voto</th><th></th></tr>
    <?php foreach ($lista as $m): ?>
      <tr>
        <td><?= e($m['nome']) ?></td>
        <td><?= e($m['cargo']) ?></td>
        <td><?= $m['direito_voto'] ? 'Sim' : 'Não' ?></td>
        <td>
          <form method="post">
            <input type="hidden" name="acao" value="remover">
            <input type="hidden" name="orgao_id" value="<?= $o['id'] ?>">
            <input type="hidden" name="membro_id" value="<?= $m['id'] ?>">
            <button>Remover</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endforeach; ?>