<?php
require_once __DIR__ . '/includes/funcoes.php';
$usuario = exigir_login();

// Configurações vindas de configJogo.php (GET), validadas também no servidor
$modo = $_GET['modo'] ?? '';
$linhas = filter_var($_GET['linhas'] ?? null, FILTER_VALIDATE_INT);
$colunas = filter_var($_GET['colunas'] ?? null, FILTER_VALIDATE_INT);

if (!in_array($modo, ['classico', 'explosivo'], true)
    || $linhas === false || $colunas === false
    || !tabuleiro_valido($linhas, $colunas)) {
    redirecionar('configJogo.php');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jogo da Toupeira - Partida</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/stylejogo.css">
  <script src="js/jogo.js" defer></script>
</head>
<body>
  <!-- Desistir: registra a partida e volta para as configurações (fluxo de telas) -->
  <header class="barra-topo">
    <a class="botao botao-voltar" id="botao-desistir" href="configJogo.php">Desistir</a>
  </header>

  <main data-jogador="<?= h($usuario['username']) ?>" data-csrf="<?= h(token_csrf()) ?>">
    <h1>
      <img src="img/logo-horizontal.png" alt="Jogo da Toupeira">
    </h1>

    <div class="area-jogo">
      <!-- Os buracos são criados pelo jogo.js a partir das configurações da URL -->
      <section class="painel painel-tabuleiro" aria-labelledby="titulo-tabuleiro">
        <h2 id="titulo-tabuleiro">&lt; Tabuleiro &gt;</h2>
        <div class="tabuleiro" id="tabuleiro" role="group" aria-label="Tabuleiro de jogo"></div>
      </section>

      <section class="painel painel-status" aria-labelledby="titulo-status">
        <h2 id="titulo-status">&lt; Partida &gt;</h2>

        <dl class="status">
          <div class="status-item">
            <dt>Modalidade</dt>
            <dd id="info-modalidade">-</dd>
          </div>
          <div class="status-item">
            <dt>Nível</dt>
            <dd id="info-nivel">-</dd>
          </div>
          <div class="status-item">
            <dt>Tempo restante</dt>
            <dd id="info-tempo">00:00</dd>
          </div>
          <div class="status-item">
            <dt>Toupeiras acertadas</dt>
            <dd id="info-acertos">0</dd>
          </div>
          <div class="status-item">
            <dt>Acertos no nível</dt>
            <dd><span id="info-percentual">0%</span> <span class="meta" id="info-necessario">(necessário: -)</span></dd>
          </div>
          <div class="status-item">
            <dt>Pontuação</dt>
            <dd id="info-pontuacao">0</dd>
          </div>
        </dl>

        <p class="aviso" id="aviso-jogo" aria-live="polite"></p>
      </section>
    </div>

    <!-- Partidas disputadas nesta página (a lista completa fica salva no banco: ver histórico) -->
    <section class="painel painel-tabela" aria-labelledby="titulo-historico">
      <h2 id="titulo-historico">&lt; Suas partidas &gt;</h2>

      <div class="tabela-rolagem">
        <table class="tabela">
          <caption>Partidas disputadas nesta página</caption>
          <thead>
            <tr>
              <th scope="col">Jogador</th>
              <th scope="col">Tabuleiro</th>
              <th scope="col">Modo</th>
              <th scope="col">Pontuação</th>
              <th scope="col">Nível</th>
              <th scope="col">Data/hora</th>
            </tr>
          </thead>
          <tbody id="corpo-historico">
            <tr id="linha-vazia"><td colspan="6">Nenhuma partida concluída ainda.</td></tr>
          </tbody>
        </table>
      </div>

      <p class="ver-mais"><a href="historico.php">Ver histórico completo</a></p>
    </section>
  </main>

  <!-- Janela de fim de partida -->
  <dialog class="dialogo-fim" id="dialogo-fim" aria-labelledby="titulo-fim">
    <h2 id="titulo-fim">Fim de jogo</h2>
    <p id="resumo-fim"></p>
    <div class="dialogo-botoes">
      <button type="button" id="botao-novo">Jogar de novo</button>
      <a class="botao" href="menu.php">Voltar ao menu</a>
    </div>
  </dialog>
</body>
</html>
