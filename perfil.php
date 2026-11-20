<?php
require_once __DIR__ . '/includes/funcoes.php';
$usuario = exigir_login();

$erros = [];
$nome = $usuario['nome'];
$telefone = formatar_telefone($usuario['telefone']);
$email = $usuario['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = campo_post('nome');
    $telefone = campo_post('telefone');
    $email = campo_post('email');
    $senha = isset($_POST['senha']) && is_string($_POST['senha']) ? $_POST['senha'] : '';

    if (!csrf_valido($_POST['csrf'] ?? null)) {
        $erros[] = 'Sessão expirada. Tente novamente.';
    } else {
        // Data de nascimento, CPF e username não mudam: só nome, telefone, e-mail e senha
        $erros = validar_dados_comuns($nome, $telefone, $email);
        if ($senha !== '' && !senha_valida($senha)) {
            $erros[] = 'A nova senha deve ter de 6 a 72 caracteres.';
        }

        if (!$erros) {
            $consulta = db()->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ?');
            $consulta->execute([$email, $usuario['id']]);
            if ($consulta->fetch()) {
                $erros[] = 'Este e-mail já está em uso por outro cadastro.';
            }
        }

        if (!$erros) {
            try {
                if ($senha !== '') {
                    $atualizar = db()->prepare('UPDATE usuarios SET nome = ?, telefone = ?, email = ?, senha_hash = ? WHERE id = ?');
                    $atualizar->execute([$nome, somente_digitos($telefone), $email, password_hash($senha, PASSWORD_DEFAULT), $usuario['id']]);
                } else {
                    $atualizar = db()->prepare('UPDATE usuarios SET nome = ?, telefone = ?, email = ? WHERE id = ?');
                    $atualizar->execute([$nome, somente_digitos($telefone), $email, $usuario['id']]);
                }
                definir_flash('sucesso', 'Dados atualizados com sucesso.');
                redirecionar('menu.php');
            } catch (PDOException $e) {
                $erros[] = 'Este e-mail já está em uso por outro cadastro.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jogo da Toupeira - Editar Dados</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/stylecadastro.css">
  <link rel="stylesheet" href="css/styleperfil.css">
</head>
<body>
  <main>
    <h1>
      <img src="img/logo.png" alt="Jogo da Toupeira">
    </h1>

    <div class="container">
<?php if ($erros): ?>
      <div class="mensagem-erro" role="alert">
        <ul>
<?php foreach ($erros as $erro): ?>
          <li><?= h($erro) ?></li>
<?php endforeach; ?>
        </ul>
      </div>
<?php endif; ?>
      <form action="perfil.php" method="post">
        <?= campo_csrf() ?>

        <div class="campo">
          <label for="nome">&lt; Nome Completo &gt;</label>
          <input type="text" id="nome" name="nome" value="<?= h($nome) ?>" maxlength="100" required>
        </div>

        <!-- readonly: o valor aparece, mas não pode ser alterado (o servidor ignora qualquer mudança) -->
        <div class="campo">
          <label for="nascimento">&lt; Data de Nascimento &gt;</label>
          <input type="date" id="nascimento" value="<?= h($usuario['nascimento']) ?>" readonly>
        </div>

        <div class="campo">
          <label for="cpf">&lt; CPF &gt;</label>
          <input type="text" id="cpf" value="<?= h(formatar_cpf($usuario['cpf'])) ?>" readonly>
        </div>

        <div class="campo">
          <label for="telefone">&lt; Telefone &gt;</label>
          <input type="tel" id="telefone" name="telefone" value="<?= h($telefone) ?>" required>
        </div>

        <div class="campo">
          <label for="email">&lt; Email &gt;</label>
          <input type="email" id="email" name="email" value="<?= h($email) ?>" maxlength="120" required>
        </div>

        <div class="campo">
          <label for="username">&lt; Nome de Usuário &gt;</label>
          <input type="text" id="username" value="<?= h($usuario['username']) ?>" readonly>
        </div>

        <div class="campo">
          <label for="senha">&lt; Nova Senha &gt;</label>
          <input type="password" id="senha" name="senha" placeholder="Em branco para manter" maxlength="72" autocomplete="new-password">
        </div>

        <p class="aviso-bloqueado">
          Data de nascimento, CPF e nome de usuário não podem ser alterados.
        </p>

        <div class="acoes">
          <button type="submit">Salvar</button>
          <a class="botao" href="menu.php">Cancelar</a>
        </div>
      </form>
    </div>
  </main>
</body>
</html>
