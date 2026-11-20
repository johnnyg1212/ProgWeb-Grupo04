<?php
require_once __DIR__ . '/includes/funcoes.php';
$usuario = exigir_login();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jogo da Toupeira - Menu Principal</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/stylemenu.css">
</head>
<body>
  <!-- Barra do topo: combobox do usuário (abre e fecha sem JavaScript) -->
  <header class="barra-topo">
    <details class="usuario-menu">
      <summary>Usuário: <?= h($usuario['username']) ?></summary>
      <ul class="opcoes-perfil">
        <li><a href="perfil.php">Editar Dados</a></li>
        <li><a href="logout.php">Sair da Conta</a></li>
      </ul>
    </details>
  </header>

  <main>
    <h1>
      <img src="img/logo.png" alt="Jogo da Toupeira">
    </h1>

<?php mostrar_flash(); ?>
    <nav class="menu-principal" aria-label="Menu principal">
      <a class="botao" href="configJogo.php">Jogar</a>
      <a class="botao" href="historico.php">Histórico de Partidas</a>
      <a class="botao" href="ranking.php">Ranking Global</a>
    </nav>
  </main>
</body>
</html>
