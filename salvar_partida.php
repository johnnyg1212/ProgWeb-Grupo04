<?php
// Recebe (via fetch, JSON) o resultado de uma partida e grava no banco.
require_once __DIR__ . '/includes/funcoes.php';

header('Content-Type: application/json; charset=UTF-8');

function responder(int $codigo, array $dados): void
{
    http_response_code($codigo);
    echo json_encode($dados);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['ok' => false, 'erro' => 'Método não permitido.']);
}

$usuario = usuario_logado();
if ($usuario === null) {
    responder(401, ['ok' => false, 'erro' => 'Não autenticado.']);
}

if (!csrf_valido($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    responder(403, ['ok' => false, 'erro' => 'Token inválido.']);
}

$dados = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($dados)) {
    responder(400, ['ok' => false, 'erro' => 'Dados inválidos.']);
}

$modo = $dados['modo'] ?? '';
$linhas = filter_var($dados['linhas'] ?? null, FILTER_VALIDATE_INT);
$colunas = filter_var($dados['colunas'] ?? null, FILTER_VALIDATE_INT);
$pontuacao = filter_var($dados['pontuacao'] ?? null, FILTER_VALIDATE_INT);
$nivel = filter_var($dados['nivel'] ?? null, FILTER_VALIDATE_INT);

if (!in_array($modo, ['classico', 'explosivo'], true)
    || $linhas === false || $colunas === false || !tabuleiro_valido($linhas, $colunas)
    || $pontuacao === false || $nivel === false
    || $nivel < 1 || $nivel > 1000
    || $pontuacao < -100000 || $pontuacao > 100000) {
    responder(422, ['ok' => false, 'erro' => 'Dados da partida inválidos.']);
}

$inserir = db()->prepare(
    'INSERT INTO partidas (usuario_id, modo, linhas, colunas, pontuacao, nivel, jogada_em)
     VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$inserir->execute([$usuario['id'], $modo, $linhas, $colunas, $pontuacao, $nivel, date('Y-m-d H:i:s')]);

responder(200, ['ok' => true]);
