<?php
require_once __DIR__ . '/includes/funcoes.php';

if (usuario_logado() !== null) {
    redirecionar('menu.php');
}

$erro = '';
$usuarioDigitado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioDigitado = campo_post('usuario');
    $senha = isset($_POST['senha']) && is_string($_POST['senha']) ? $_POST['senha'] : '';

    if (!csrf_valido($_POST['csrf'] ?? null)) {
        $erro = 'Sessão expirada. Tente novamente.';
    } else {
        $consulta = db()->prepare('SELECT id, senha_hash FROM usuarios WHERE username = ?');
        $consulta->execute([$usuarioDigitado]);
        $registro = $consulta->fetch();

        if ($registro && password_verify($senha, $registro['senha_hash'])) {
            iniciar_sessao_usuario((int) $registro['id']);
            redirecionar('menu.php');
        }
        $erro = 'Usuário ou senha incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jogo da Toupeira - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main>
    <h1>
        <img src="img/logo.png" alt="Logo da Toupeira">
    </h1>
    <div class="container">
<?php mostrar_flash(); ?>
<?php if ($erro !== ''): ?>
      <p class="mensagem-erro" role="alert"><?= h($erro) ?></p>
<?php endif; ?>
      <form action="index.php" method="post">
        <?= campo_csrf() ?>

        <div class="campo">
          <label for="usuario">&lt; Usuário &gt;</label>
          <input type="text" id="usuario" name="usuario" value="<?= h($usuarioDigitado) ?>" required>
        </div>

        <div class="campo">
          <label for="senha">&lt; Senha &gt;</label>
          <input type="password" id="senha" name="senha" required>
        </div>

        <button type="submit">Entrar</button>
      </form>

      <p class="link-cadastro">
        <a href="cadastro.php">Não possui cadastro ainda? Cadastre-se</a>
      </p>
    </div>
  </main>
</body>
</html>
