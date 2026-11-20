<?php
require_once __DIR__ . '/includes/funcoes.php';
exigir_login();

// Top 10 jogadores de cada modalidade: vale a MELHOR partida de cada jogador
// (maior pontuação; desempate por nível e, depois, pela data mais antiga)
function top10(string $modo): array
{
    $consulta = db()->prepare(
        'SELECT username, nivel, linhas, colunas, jogada_em, pontuacao FROM (
             SELECT u.username, p.nivel, p.linhas, p.colunas, p.jogada_em, p.pontuacao,
                    ROW_NUMBER() OVER (
                        PARTITION BY p.usuario_id
                        ORDER BY p.pontuacao DESC, p.nivel DESC, p.jogada_em ASC, p.id ASC
                    ) AS melhor
             FROM partidas p JOIN usuarios u ON u.id = p.usuario_id
             WHERE p.modo = ?
         ) t
         WHERE melhor = 1
         ORDER BY pontuacao DESC, nivel DESC, jogada_em ASC
         LIMIT 10'
    );
    $consulta->execute([$modo]);
    return $consulta->fetchAll();
}

function tabela_ranking(string $classe, string $titulo, array $linhas): void
{
    echo '        <table class="tabela ' . h($classe) . "\">\n";
    echo '          <caption>' . h($titulo) . "</caption>\n";
    echo "          <thead>\n            <tr>\n";
    foreach (['Posição', 'Username', 'Nível', 'Buracos', 'Data', 'Pontuação'] as $coluna) {
        echo '              <th scope="col">' . $coluna . "</th>\n";
    }
    echo "            </tr>\n          </thead>\n          <tbody>\n";
    for ($i = 0; $i < 10; $i++) {
        $p = $linhas[$i] ?? null;
        echo '            <tr><td>' . ($i + 1) . 'º</td>';
        if ($p === null) {
            echo '<td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>';
        } else {
            echo '<td>' . h($p['username']) . '</td>'
               . '<td>' . (int) $p['nivel'] . '</td>'
               . '<td>' . (int) $p['linhas'] . ' &times; ' . (int) $p['colunas'] . '</td>'
               . '<td>' . h(formatar_data_hora($p['jogada_em'])) . '</td>'
               . '<td>' . (int) $p['pontuacao'] . '</td>';
        }
        echo "</tr>\n";
    }
    echo "          </tbody>\n        </table>\n";
}

$rankingClassico = top10('classico');
$rankingExplosivo = top10('explosivo');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jogo da Toupeira - Ranking Global</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/styleranking.css">
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
      <!-- Os radios ficam antes do cabeçalho e das tabelas para o CSS trocar a tabela sem JavaScript -->
      <input type="radio" id="ranking-classico" name="modo" value="classico" checked>
      <input type="radio" id="ranking-explosivo" name="modo" value="explosivo">

      <div class="cabecalho-painel">
        <h2>&lt; Ranking Global &gt;</h2>

        <div class="filtro-modo">
          <span>Modo:</span>
          <label for="ranking-classico">Clássico</label>
          <label for="ranking-explosivo">Explosivo</label>
        </div>
      </div>

      <div class="tabela-rolagem">
<?php
tabela_ranking('tabela-classico', 'Top 10 - Modo Clássico', $rankingClassico);
tabela_ranking('tabela-explosivo', 'Top 10 - Modo Explosivo', $rankingExplosivo);
?>
      </div>
    </section>
  </main>
</body>
</html>
