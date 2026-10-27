<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'orgao') {
        executa('INSERT INTO orgaos (nome, tipo) VALUES (?, ?)',
                [trim($_POST['nome']), $_POST['tipo']]);
    } elseif ($acao === 'membro') {
        $nome     = trim($_POST['nome'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');

        // orgaos[ID] = marcado; cargo[ID] = texto; voto[ID] = marcado
        $orgaosSel = $_POST['orgaos'] ?? [];
        $cargos    = $_POST['cargo'] ?? [];
        $votos     = $_POST['voto'] ?? [];

        if ($nome === '' || !$orgaosSel) {
            header('Location: index.php?p=orgaos&erro=1');
            exit;
        }

        $db->begin_transaction();
        try {
            executa(
                'INSERT INTO membros (nome, email, telefone) VALUES (?, ?, ?)',
                [$nome, $email, $telefone]
            );
            $membroId = (int)$db->insert_id;

            foreach (array_keys($orgaosSel) as $orgaoId) {
                $orgaoId = (int)$orgaoId;
                executa(
                    'INSERT INTO membro_orgao (membro_id, orgao_id, cargo, direito_voto)
                     VALUES (?, ?, ?, ?)',
                    [
                        $membroId,
                        $orgaoId,
                        trim($cargos[$orgaoId] ?? ''),
                        isset($votos[$orgaoId]) ? 1 : 0,
                    ]
                );
            }

            $db->commit();
        } catch (Throwable $ex) {
            $db->rollback();
            throw $ex;
        }
    } elseif ($acao === 'remover') {
        executa('DELETE FROM membro_orgao WHERE orgao_id = ? AND membro_id = ?',
                [(int)$_POST['orgao_id'], (int)$_POST['membro_id']]);
    }
    header('Location: index.php?p=orgaos');
    exit;
}

$orgaos = consulta('SELECT * FROM orgaos WHERE ativo = 1 ORDER BY nome');
$tipos  = [
    'JUNTA_FREGUESIA'      => 'Junta de Freguesia',
    'ASSEMBLEIA_FREGUESIA' => 'Assembleia de Freguesia',
    'COMISSAO_AVALIACAO'   => 'Comissão de Avaliação',
    'CONSELHO_COORDENADOR' => 'Conselho Coordenador de Avaliação',
    'OUTRO'                => 'Outro',
];
?>
<h1>Órgãos e Membros</h1>

<?php if (isset($_GET['erro'])): ?>
  <p><strong>Indica o nome e escolhe pelo menos um órgão.</strong></p>
<?php endif; ?>

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

  <h3>Órgãos</h3>
  <?php foreach ($orgaos as $o): ?>
    <div>
      <label>
        <input type="checkbox" name="orgaos[<?= (int)$o['id'] ?>]" value="1">
        <?= e($o['nome']) ?>
      </label>
      <input name="cargo[<?= (int)$o['id'] ?>]" placeholder="Cargo (ex: Presidente)">
      <label>
        <input type="checkbox" name="voto[<?= (int)$o['id'] ?>]" checked> Direito a voto
      </label>
    </div>
  <?php endforeach; ?>

  <button>Criar membro</button>
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