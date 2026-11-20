<?php
require_once __DIR__ . '/includes/funcoes.php';
$usuario = exigir_login();

$consulta = db()->prepare(
    'SELECT modo, linhas, colunas, pontuacao, nivel, jogada_em
     FROM partidas WHERE usuario_id = ?
     ORDER BY jogada_em DESC, id DESC LIMIT 200'
);
$consulta->execute([$usuario['id']]);
$partidas = $consulta->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jogo da Toupeira - Histórico de Partidas</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/stylehistorico.css">
</head>
<body>
  <header class="barra-topo">
    <a class="botao botao-voltar" href="menu.php">Voltar</a>
  </header>

  <main>
    <h1>
      <img src="img/logo-horizontal.png" alt="Jogo da Toupeira">
    </h1>

    <section class="painel-tabela">
      <h2>&lt; Histórico de Partidas &gt;</h2>

      <div class="tabela-rolagem">
        <table class="tabela">
          <caption>Partidas jogadas</caption>
          <thead>
            <tr>
              <th scope="col">Jogador</th>
              <th scope="col">Buracos</th>
              <th scope="col">Modo</th>
              <th scope="col">Pontuação</th>
              <th scope="col">Nível</th>
              <th scope="col">Data</th>
            </tr>
          </thead>
          <tbody>
<?php if (!$partidas): ?>
            <tr><td colspan="6">Nenhuma partida registrada ainda.</td></tr>
<?php endif; ?>
<?php foreach ($partidas as $p): ?>
            <tr>
              <td><?= h($usuario['username']) ?></td>
              <td><?= (int) $p['linhas'] ?> &times; <?= (int) $p['colunas'] ?></td>
              <td><?= h(nome_modo($p['modo'])) ?></td>
              <td><?= (int) $p['pontuacao'] ?></td>
              <td><?= (int) $p['nivel'] ?></td>
              <td><?= h(formatar_data_hora($p['jogada_em'])) ?></td>
            </tr>
<?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
