<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$retorno = require __DIR__ . '/config/database.php';

$db = null;
if ($retorno instanceof mysqli) {
    $db = $retorno;
} else {
    foreach (get_defined_vars() as $v) {
        if ($v instanceof mysqli) {
            $db = $v;
            break;
        }
    }
}
if (!$db) {
    die('Não foi encontrada nenhuma ligação mysqli em app/config/database.php');
}

$db->set_charset('utf8mb4');

function e($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function consulta(string $sql, array $p = []): array {
    global $db;
    $st = $db->prepare($sql);
    if ($p) {
        $st->bind_param(str_repeat('s', count($p)), ...$p);
    }
    $st->execute();
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}

function executa(string $sql, array $p = []): void {
    global $db;
    $st = $db->prepare($sql);
    if ($p) {
        $st->bind_param(str_repeat('s', count($p)), ...$p);
    }
    $st->execute();
}