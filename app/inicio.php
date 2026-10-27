<?php
$totalOrgaos   = (int)consulta('SELECT COUNT(*) AS n FROM orgaos WHERE ativo = 1')[0]['n'];
$totalMembros  = (int)consulta('SELECT COUNT(*) AS n FROM membros WHERE ativo = 1')[0]['n'];
$totalAgendadas = (int)consulta("SELECT COUNT(*) AS n FROM reunioes WHERE estado = 'AGENDADA'")[0]['n'];
$totalFinalizadas = (int)consulta("SELECT COUNT(*) AS n FROM reunioes WHERE estado = 'FINALIZADA'")[0]['n'];

$proximas = consulta(
    "SELECT r.id, r.titulo, r.data_hora, r.local_reuniao, r.estado, o.nome AS orgao
     FROM reunioes r JOIN orgaos o ON o.id = r.orgao_id
     WHERE r.estado IN ('AGENDADA', 'EM_CURSO')
     ORDER BY r.data_hora ASC
     LIMIT 5"
);
?>
<h1>Gestão de Atas</h1>
<p>Bem-vindo. Aqui podes gerir os órgãos, os membros e as reuniões da freguesia.</p>

<div class="cartoes">
  <div class="cartao">
    <span class="numero"><?= $totalOrgaos ?></span>
    <span class="rotulo">Órgãos</span>
  </div>
  <div class="cartao">
    <span class="numero"><?= $totalMembros ?></span>
    <span class="rotulo">Membros</span>
  </div>
  <div class="cartao">
    <span class="numero"><?= $totalAgendadas ?></span>
    <span class="rotulo">Reuniões agendadas</span>
  </div>
  <div class="cartao">
    <span class="numero"><?= $totalFinalizadas ?></span>
    <span class="rotulo">Reuniões finalizadas</span>
  </div>
</div>

<h2>Atalhos</h2>
<div class="atalhos">
  <a href="index.php?p=reunioes">Agendar reunião</a>
  <a href="index.php?p=orgaos">Novo membro</a>
  <a href="index.php?p=orgaos">Ver composição dos órgãos</a>
</div>

<h2>Próximas reuniões</h2>
<?php if (!$proximas): ?>
  <p>Não há reuniões agendadas.</p>
<?php else: ?>
  <table>
    <tr><th>Data</th><th>Órgão</th><th>Título</th><th>Local</th><th>Estado</th><th></th></tr>
    <?php foreach ($proximas as $r): ?>
      <tr>
        <td><?= e(date('d/m/Y H:i', strtotime($r['data_hora']))) ?></td>
        <td><?= e($r['orgao']) ?></td>
        <td><?= e($r['titulo']) ?></td>
        <td><?= e($r['local_reuniao']) ?></td>
        <td><?= e(ucfirst(strtolower(str_replace('_', ' ', $r['estado'])))) ?></td>
        <td><a href="index.php?p=pontos&reuniao=<?= (int)$r['id'] ?>">Ordem do dia</a></td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>