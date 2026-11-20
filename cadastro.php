<?php
require_once __DIR__ . '/includes/funcoes.php';

if (usuario_logado() !== null) {
    redirecionar('menu.php');
}

$erros = [];
$v = ['nome' => '', 'nascimento' => '', 'cpf' => '', 'telefone' => '', 'email' => '', 'username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($v) as $campo) {
        $v[$campo] = campo_post($campo);
    }
    $senha = isset($_POST['senha']) && is_string($_POST['senha']) ? $_POST['senha'] : '';

    if (!csrf_valido($_POST['csrf'] ?? null)) {
        $erros[] = 'Sessão expirada. Tente novamente.';
    } else {
        $erros = validar_dados_comuns($v['nome'], $v['telefone'], $v['email']);
        if (!data_nascimento_valida($v['nascimento'])) {
            $erros[] = 'Informe uma data de nascimento válida.';
        }
        if (!cpf_valido($v['cpf'])) {
            $erros[] = 'CPF inválido.';
        }
        if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $v['username'])) {
            $erros[] = 'O nome de usuário deve ter de 3 a 20 letras, números ou _.';
        }
        if (!senha_valida($senha)) {
            $erros[] = 'A senha deve ter de 6 a 72 caracteres.';
        }

        if (!$erros) {
            $cpf = somente_digitos($v['cpf']);
            $consulta = db()->prepare('SELECT cpf, email, username FROM usuarios WHERE cpf = ? OR email = ? OR username = ?');
            $consulta->execute([$cpf, $v['email'], $v['username']]);
            foreach ($consulta->fetchAll() as $existente) {
                if ($existente['cpf'] === $cpf) {
                    $erros[] = 'Já existe um cadastro com este CPF.';
                }
                if (strcasecmp($existente['email'], $v['email']) === 0) {
                    $erros[] = 'Já existe um cadastro com este e-mail.';
                }
                if (strcasecmp($existente['username'], $v['username']) === 0) {
                    $erros[] = 'Este nome de usuário já está em uso.';
                }
            }
            $erros = array_values(array_unique($erros));
        }

        if (!$erros) {
            try {
                $inserir = db()->prepare(
                    'INSERT INTO usuarios (nome, nascimento, cpf, telefone, email, username, senha_hash, criado_em)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $inserir->execute([
                    $v['nome'], $v['nascimento'], somente_digitos($v['cpf']),
                    somente_digitos($v['telefone']), $v['email'], $v['username'],
                    password_hash($senha, PASSWORD_DEFAULT), date('Y-m-d H:i:s'),
                ]);
                definir_flash('sucesso', 'Cadastro realizado! Faça login para jogar.');
                redirecionar('index.php');
            } catch (PDOException $e) {
                // Dois cadastros iguais ao mesmo tempo (chave única do banco)
                $erros[] = 'CPF, e-mail ou nome de usuário já cadastrado.';
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
  <title>Jogo da Toupeira - Cadastro</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/stylecadastro.css">
</head>
<body>
  <main>
    <h1>
      <img src="img/logo.png" alt="Logo da Toupeira">
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
      <form action="cadastro.php" method="post">
        <?= campo_csrf() ?>

        <div class="campo">
          <label for="nome">&lt; Nome Completo &gt;</label>
          <input type="text" id="nome" name="nome" value="<?= h($v['nome']) ?>" maxlength="100" required>
        </div>

        <div class="campo">
          <label for="nascimento">&lt; Data de Nascimento &gt;</label>
          <input type="date" id="nascimento" name="nascimento" value="<?= h($v['nascimento']) ?>" required>
        </div>

        <div class="campo">
          <label for="cpf">&lt; CPF &gt;</label>
          <input type="text" id="cpf" name="cpf" value="<?= h($v['cpf']) ?>" placeholder="000.000.000-00" maxlength="14" inputmode="numeric" required>
        </div>

        <div class="campo">
          <label for="telefone">&lt; Telefone &gt;</label>
          <input type="tel" id="telefone" name="telefone" value="<?= h($v['telefone']) ?>" placeholder="(19) 99999-9999" required>
        </div>

        <div class="campo">
          <label for="email">&lt; Email &gt;</label>
          <input type="email" id="email" name="email" value="<?= h($v['email']) ?>" maxlength="120" required>
        </div>

        <div class="campo">
          <label for="username">&lt; Nome de Usuário &gt;</label>
          <input type="text" id="username" name="username" value="<?= h($v['username']) ?>" maxlength="20" required>
        </div>

        <div class="campo">
          <label for="senha">&lt; Senha &gt;</label>
          <input type="password" id="senha" name="senha" minlength="6" maxlength="72" autocomplete="new-password" required>
        </div>

        <button type="submit">Cadastrar</button>
      </form>

      <p class="link-cadastro">
        <a href="index.php">Já possui cadastro? Entrar</a>
      </p>
    </div>
  </main>
</body>
</html>
