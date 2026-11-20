<?php
// Funções de apoio: banco de dados, sessão, segurança e validações.
require_once __DIR__ . '/config.php';

/* ---------- Sessão ---------- */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

/* ---------- Banco de dados (PDO) ---------- */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">'
               . '<title>Erro - Jogo da Toupeira</title></head><body>'
               . '<p>Não foi possível conectar ao banco de dados. Verifique includes/config.php.</p>'
               . '</body></html>';
            exit;
        }
    }
    return $pdo;
}

/* ---------- Saída segura ---------- */
function h($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirecionar(string $destino): void
{
    header('Location: ' . $destino);
    exit;
}

/* ---------- Mensagens entre páginas (flash) ---------- */
function definir_flash(string $tipo, string $texto): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

function pegar_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function mostrar_flash(): void
{
    $flash = pegar_flash();
    if ($flash !== null) {
        $classe = $flash['tipo'] === 'erro' ? 'mensagem-erro' : 'mensagem-sucesso';
        echo '<p class="' . $classe . '" role="alert">' . h($flash['texto']) . "</p>\n";
    }
}

/* ---------- Autenticação ---------- */
function usuario_logado(): ?array
{
    if (empty($_SESSION['usuario_id'])) {
        return null;
    }
    static $usuario = false;
    if ($usuario === false) {
        $consulta = db()->prepare('SELECT * FROM usuarios WHERE id = ?');
        $consulta->execute([$_SESSION['usuario_id']]);
        $usuario = $consulta->fetch() ?: null;
        if ($usuario === null) {
            unset($_SESSION['usuario_id']);
        }
    }
    return $usuario;
}

function exigir_login(): array
{
    $usuario = usuario_logado();
    if ($usuario === null) {
        redirecionar('index.php');
    }
    return $usuario;
}

function iniciar_sessao_usuario(int $id): void
{
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $id;
}

/* ---------- CSRF ---------- */
function token_csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function campo_csrf(): string
{
    return '<input type="hidden" name="csrf" value="' . h(token_csrf()) . '">';
}

function csrf_valido(?string $token): bool
{
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/* ---------- Dados do formulário ---------- */
function campo_post(string $nome): string
{
    $valor = $_POST[$nome] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

function somente_digitos(string $texto): string
{
    return preg_replace('/\D+/', '', $texto);
}

/* ---------- Validações ---------- */
function cpf_valido(string $cpf): bool
{
    $cpf = somente_digitos($cpf);
    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    for ($t = 9; $t < 11; $t++) {
        $soma = 0;
        for ($i = 0; $i < $t; $i++) {
            $soma += (int) $cpf[$i] * ($t + 1 - $i);
        }
        $digito = ((10 * $soma) % 11) % 10;
        if ((int) $cpf[$t] !== $digito) {
            return false;
        }
    }
    return true;
}

function data_nascimento_valida(string $data): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $data);
    if ($d === false || $d->format('Y-m-d') !== $data) {
        return false;
    }
    $hoje = new DateTime('today');
    $limite = (new DateTime('today'))->modify('-120 years');
    return $d <= $hoje && $d >= $limite;
}

function telefone_valido(string $telefone): bool
{
    $digitos = somente_digitos($telefone);
    return strlen($digitos) === 10 || strlen($digitos) === 11;
}

function formatar_cpf(string $cpf): string
{
    return preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $cpf);
}

function formatar_telefone(string $tel): string
{
    if (strlen($tel) === 11) {
        return preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $tel);
    }
    return preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', $tel);
}

function formatar_data_hora(string $data): string
{
    return date('d/m/Y H:i', strtotime($data));
}

function nome_modo(string $modo): string
{
    return $modo === 'classico' ? 'Clássico' : 'Explosivo';
}

function tabuleiro_valido(int $linhas, int $colunas): bool
{
    $total = $linhas * $colunas;
    return $linhas >= 1 && $colunas >= 1 && $total >= MIN_BURACOS && $total <= MAX_BURACOS;
}

/**
 * Valida os dados comuns de cadastro e edição.
 * Retorna a lista de mensagens de erro (vazia se estiver tudo certo).
 */
function validar_dados_comuns(string $nome, string $telefone, string $email): array
{
    $erros = [];
    $tamanhoNome = mb_strlen($nome, 'UTF-8');
    if ($tamanhoNome < 3 || $tamanhoNome > 100) {
        $erros[] = 'O nome completo deve ter entre 3 e 100 caracteres.';
    }
    if (!telefone_valido($telefone)) {
        $erros[] = 'Informe um telefone com DDD (10 ou 11 dígitos).';
    }
    if (mb_strlen($email, 'UTF-8') > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Informe um e-mail válido.';
    }
    return $erros;
}

function senha_valida(string $senha): bool
{
    return strlen($senha) >= 6 && strlen($senha) <= 72;
}
