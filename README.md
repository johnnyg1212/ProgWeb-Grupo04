# 🐹 Jogo da Toupeira

Projeto Final de **Programação para a Web** (FT-UNICAMP, 2º semestre de 2026), sob orientação do Prof. Guilherme Palermo Coelho.

Plataforma online do jogo **Acerte a Toupeira** (*Whac-a-Mole*), com cadastro e login de jogadores, partidas em duas modalidades (**Clássica** e **Explosiva**), histórico de partidas e ranking global.

**Site publicado:** https://prog-web-grupo04.vercel.app/ <br>
**Parcial 1 - vídeo no YouTube:** https://www.youtube.com/watch?v=xYdes7JchXw <br>
**Parcial 2 - vídeo no YouTube:** https://youtu.be/QOVDdj5rpsw

---

## 👥 Grupo 04

| Integrante | RA | Parcial 1 | Parcial 2 | Parcial 3 |
|---|---|---|---|---|
| João Guilherme Fernandes Frota | 222968 | Criação do repositório e administração do grupo; fluxo de telas e protótipo no Figma; telas de configurações do jogo, partida e edição de dados; testes e validação | Desenvolvimento do jogo.js (regras e dificuldade, tabuleiro, cliques, timers, níveis, fim de partida e histórico), com apoio de IA generativa na escrita do código; CSS do tabuleiro e da página da partida; testes (automáticos, manuais e validação W3C); GitHub e Vercel. | Conferir o modelo das tabelas e a consulta do ranking (um jogador por linha), testando com mais usuários |
| Heitor Roberto Mesquita de Souza | 236168 | Diagramação do fluxo de telas e protótipo no Figma; telas de login, cadastro e menu | Páginas jogo.php e configJogo.php (contêiner do tabuleiro, painel da partida, tabela de histórico, janela de fim e área de erro) e alt dos logos; validação do formulário de configuração (configurar.js), com Cicero e Henrique; testes manuais do jogo. | Testar todas as validações (CPF, e-mail e usuário repetidos, campos bloqueados) e registrar os resultados |
| Cicero Eduardo Campos Leite dos Santos | 250984 | Apoio na organização do desenvolvimento; telas de histórico e ranking |CSS da janela de fim de partida e da mensagem de erro da configuração; validação do formulário de configuração (configurar.js), com Heitor e Henrique; testes manuais do jogo. | Estudar o fluxo completo (hash de senha, sessão, token CSRF) e ser o contato para dúvidas técnicas do grupo e atualizar o que cada um fez no README |
| Davi de Paula Garcia | 252508 | Revisão dos códigos e README |Documentação do projeto: atualização do README da Parcial 2.| Testar o fluxo entre telas, a proteção das páginas sem login e o botão Desistir |
| Henrique Carvalho De Mello | 241120 | README | CSS dos ajustes da página da partida para celular; validação do formulário de configuração (configurar.js), com Heitor e Cicero; testes manuais do jogo. | Rodar os validadores nas páginas |

---

## 📌 Andamento do projeto

| Entrega | Escopo | Situação |
|---|---|---|
| **Parcial 1** | Front-end em HTML e CSS, versão **não funcional**: todas as páginas, formatação final e navegação final | ✅ Concluída |
| **Parcial 2** | Dinâmica completa do jogo em JavaScript (sem back-end) | ✅ Concluída |
| **Parcial 3** | Back-end em PHP com MySQL/MariaDB: autenticação com sessões, histórico e ranking | ✅ Concluída |

> **Parcial 3:** o jogo agora tem **controle de acesso e armazenamento de dados**. Para jogar é preciso se **cadastrar** e fazer **login**; só as páginas de login e cadastro são abertas a quem não entrou. As páginas de menu, configurações, partida, histórico, ranking e edição de dados exigem usuário logado e levam de volta ao login quando ele não existe ou saiu da conta. Os usuários e as partidas ficam salvos em um **banco de dados MySQL/MariaDB**: ao terminar ou desistir de uma partida, o resultado é enviado ao servidor e gravado, e **não some mais** ao recarregar ou sair da página. O **histórico completo** mostra as partidas do jogador logado, e o **ranking global** mostra o top 10 de cada modalidade, com a melhor partida de cada jogador. A tabela "Suas partidas" da página do jogo continua mostrando só as partidas jogadas naquela visita. A **edição de dados** permite alterar nome, telefone, e-mail e senha; CPF, data de nascimento e nome de usuário não mudam.
>
> Parte do código PHP da Parcial 3 foi escrita com apoio de IA generativa (Claude) e revisada e testada pelo grupo. As senhas são guardadas com hash e os formulários têm proteção contra CSRF.

---

## 🎮 Como o jogo funciona

1. Na tela de **configurações**, o jogador escolhe a **modalidade** e as **dimensões do tabuleiro** (linhas × colunas, com **4 a 64 buracos no total**).
2. Na **partida**, toupeiras (e bombas, no modo Explosivo) surgem em buracos sorteados, **uma de cada vez**. O jogador clica nas toupeiras antes que elas se escondam.
3. Cada **nível dura 20 segundos**. No fim do nível, o jogo confere a porcentagem de acertos:
   - **passou:** começa o próximo nível, com toupeiras visíveis por menos tempo;
   - **não passou:** a partida termina e o jogador escolhe entre **Jogar de novo** e **Voltar ao menu**.
4. **Desistir** registra a partida parcial e volta para as configurações.

### Modalidades

| Modalidade | Regra |
|---|---|
| **Clássica** | Só aparecem toupeiras |
| **Explosiva** | Aparecem toupeiras e bombas (30% de chance). Clicar em uma bomba tira 1 ponto; ignorar a bomba não tem custo |

### Dificuldade e regras de nível

| Regra | Valor |
|---|---|
| Duração de cada nível | 20 s |
| Tempo visível de cada toupeira | 1,5 s no nível 1, cai 0,15 s por nível, com mínimo de 0,4 s |
| Acerto necessário para passar | 70% no nível 1, **+2 pontos por nível**, com **limite de 90%** |
| Porcentagem do nível | (toupeiras acertadas − bombas clicadas) ÷ toupeiras exibidas |
| Pontuação da partida | soma, nível a nível, de toupeiras acertadas − bombas clicadas (no modo Explosivo pode ser negativa) |
| Toupeira "exibida" | conta quando termina (acertada ou escondida). Nenhum alvo novo surge se não couber inteiro no tempo que resta do nível |

Todos esses valores ficam no começo do arquivo `js/jogo.js`.

### Informações exibidas durante a partida

Modalidade, nível, tempo restante, toupeiras acertadas, porcentagem de acertos no nível (com a porcentagem necessária) e pontuação. Abaixo do tabuleiro, a tabela **Suas partidas** lista as partidas encerradas na página (jogador, tabuleiro, modo, pontuação, nível e data/hora).

---

## 🗺️ Páginas e fluxo de navegação

| Página | Arquivo | Estilos | Script |
|---|---|---|---|
| Login (página inicial) | `index.php` | `style.css` | |
| Cadastro | `cadastro.php` | `style.css` + `stylecadastro.css` | |
| Menu principal | `menu.php` | `style.css` + `stylemenu.css` | |
| Configurações da partida | `configJogo.php` | `style.css` + `styleconfigJogo.css` | `configurar.js` |
| Partida | `jogo.php` | `style.css` + `stylejogo.css` | `jogo.js` |
| Histórico de partidas | `historico.php` | `style.css` + `stylehistorico.css` | |
| Ranking global | `ranking.php` | `style.css` + `styleranking.css` | |
| Edição de dados do usuário | `perfil.php` | `style.css` + `stylecadastro.css` + `styleperfil.css` | |

```mermaid
flowchart TD
    login["Login<br>index.php"]
    cadastro["Cadastro<br>cadastro.php"]
    menu["Menu<br>menu.php"]
    config["Configurações<br>configJogo.php"]
    jogo["Partida<br>jogo.php"]
    historico["Histórico<br>historico.php"]
    ranking["Ranking<br>ranking.php"]
    perfil["Editar dados<br>perfil.php"]

    login -->|Entrar| menu
    login -->|Cadastre-se| cadastro
    cadastro -->|Cadastrar| login
    menu -->|Jogar| config
    menu -->|Histórico de Partidas| historico
    menu -->|Ranking Global| ranking
    menu -->|Editar Dados| perfil
    menu -->|Sair da Conta| login
    config -->|Começar| jogo
    config -->|Voltar| menu
    jogo -->|Desistir| config
    jogo -->|Fim: Jogar de novo| jogo
    jogo -->|Fim: Voltar ao menu| menu
    jogo -->|Ver histórico completo| historico
    historico -->|Voltar| menu
    ranking -->|Voltar| menu
    perfil -->|Salvar ou Cancelar| menu
```

### Decisões de navegação

- **Menu principal entre o login e as configurações:** reúne Jogar, Histórico, Ranking e Edição de dados em um só lugar.
- **Histórico em duas versões:** a página da partida mostra as partidas jogadas naquela página; o histórico completo fica em página própria (`historico.php`).
- **Configurações passam para o jogo pela URL:** o formulário envia por GET (`jogo.php?modo=classico&linhas=4&colunas=4`) e o jogo lê esses valores. Se a URL tiver valores inválidos, o jogo volta para as configurações.

---

## 🧩 Como o JavaScript está organizado

| Arquivo | Responsabilidade |
|---|---|
| `js/configurar.js` | Valida o tamanho do tabuleiro (4 a 64 buracos no total) antes de abrir a partida |
| `js/jogo.js` | Estado, regras, tabuleiro, timers, cliques, níveis, fim de partida e histórico da página |

Pontos principais de `jogo.js`:

- **Tabuleiro:** os buracos (`<button>`) são criados pelo JavaScript a partir de linhas × colunas. O número de colunas e o tamanho do buraco vão para o CSS por variáveis (`--colunas` e `--tam`).
- **Cliques:** um único ouvinte no tabuleiro (delegação de eventos) trata acerto de toupeira, clique em bomba e buraco vazio.
- **Estilo pelo CSS:** o JavaScript só liga e desliga classes (`tem-toupeira`, `tem-bomba`, `acerto`, `erro-bomba`); a aparência fica no `stylejogo.css`.
- **Timers:** um relógio (`setInterval`, a cada 100 ms) que calcula o tempo restante pelo horário do computador; uma cadeia de `setTimeout` para as aparições; e um `setTimeout` por alvo para escondê-lo. Todos são cancelados ao fim da partida, ao desistir e ao sair da página.
- **Vitória e derrota:** ao fim de cada nível, `encerrarNivel` compara a porcentagem do nível com o mínimo exigido. Quem não atinge o mínimo encerra a partida.
- **Fim de partida:** usa o elemento `<dialog>` com as opções "Jogar de novo" e "Voltar ao menu".

---

## 🗂️ Estrutura do projeto

```
.
├── index.php
├── cadastro.php
├── menu.php
├── configJogo.php
├── jogo.php
├── historico.php
├── ranking.php
├── perfil.php
├── css/
│   ├── style.css            # Base: fonte, fundo, botões, formulários
│   ├── stylecadastro.css
│   ├── stylemenu.css
│   ├── styleconfigJogo.css
│   ├── stylejogo.css
│   ├── stylehistorico.css
│   ├── styleranking.css
│   └── styleperfil.css
├── js/
│   ├── configurar.js
│   └── jogo.js
├── font/
│   └── Matcha.ttf
└── img/                     # Logos, fundo e mascote
```

---

## ▶️ Como executar (Parcial 3: precisa de PHP e MySQL/MariaDB)

1. Instale o **XAMPP** (Apache + PHP 8 + MariaDB) ou equivalente e inicie Apache e MySQL.
2. Clone o repositório dentro da pasta `htdocs` (ex.: `htdocs/toupeira`).
3. Crie o banco importando `sql/schema.sql` (phpMyAdmin → Importar, ou `mysql -u root -p < sql/schema.sql`).
4. O projeto já vem configurado em `includes/config.php` com usuário `root` e a senha de teste do grupo. Para funcionar sem alterar nada, defina **a mesma senha** no `root` do seu MySQL (a que está em `DB_PASS`). Se o seu MySQL usar outra senha, basta trocar o valor de `DB_PASS` no seu computador, sem enviá-lo ao GitHub.
5. Acesse `http://localhost/toupeira/index.php`, clique em **Cadastre-se**, crie uma conta, faça login e jogue.

Alternativa sem Apache: na pasta do projeto, rode `php -S localhost:8000` e abra `http://localhost:8000`.

### Back-end em PHP

| Arquivo | Função |
|---|---|
| `includes/config.php` | Dados de conexão com o banco (usuário e senha de teste do grupo) e regras do tabuleiro |
| `includes/funcoes.php` | Conexão PDO, sessão, CSRF, validações (CPF, e-mail, telefone...) e mensagens |
| `index.php` / `logout.php` | Login (`password_verify`, sessão regenerada) e saída da conta |
| `cadastro.php` | Cadastro com validação no servidor, CPF/e-mail/usuário únicos, senha com `password_hash` |
| `perfil.php` | Edição de nome, telefone, e-mail e senha (CPF, nascimento e username são só leitura) |
| `jogo.php` + `salvar_partida.php` | Partida (valida a URL no servidor); o JS envia o resultado por `fetch` e o PHP grava em `partidas` |
| `historico.php` | Partidas do usuário logado, da mais recente para a mais antiga |
| `ranking.php` | Top 10 jogadores por modalidade (melhor partida de cada jogador; desempate por nível e data) |
| `sql/schema.sql` | Tabelas `usuarios` e `partidas` |

Todas as páginas, exceto login e cadastro, exigem usuário logado (senão redirecionam para `index.php`). As consultas usam *prepared statements*, a saída é escapada com `htmlspecialchars` e os formulários POST usam token CSRF.

### Como testar o jogo

| Teste | Resultado esperado |
|---|---|
| Em Configurações, informar 1 × 3 ou 1 × 80 | Mensagem de erro e a partida não abre |
| Abrir `jogo.php` sem parâmetros na URL | Volta para as configurações |
| Jogar clicando em todas as toupeiras | Passa de nível; a meta sobe de 70% para 72%, 74%... até parar em 90% |
| Jogar sem clicar | Ao fim do nível, a partida termina e a janela de fim aparece |
| Modo Explosivo: clicar em bombas | A pontuação diminui 1 por bomba |
| "Jogar de novo" | Zera nível, pontuação e acertos, e a nova partida aparece na tabela |
| **Desistir** | Volta para as configurações |
| Tabuleiros 2 × 2, 8 × 8 e 1 × 64 | Os buracos aparecem; o 1 × 64 rola na horizontal |

---

## ✅ Padrões e validação

- **HTML5** (`<!DOCTYPE html>`), com elementos semânticos, validado em [validator.w3.org](https://validator.w3.org/).
- **CSS** em folhas **externas** (uma base e uma por página), validado em [jigsaw.w3.org/css-validator](https://jigsaw.w3.org/css-validator/).
- **JavaScript** em arquivos externos (`js/`), sem bibliotecas.
- **Nenhum template CSS** foi utilizado.
- Recursos de interface feitos **só com HTML e CSS**: menu do usuário (`<details>`), cartões de modalidade (`<input type="radio">` + `:checked`) e troca de tabela no ranking.
- Layout responsivo com `flexbox`, `grid` e `@media`.

---

## 🛠️ Tecnologias

| Etapa | Tecnologias |
|---|---|
| Parcial 1 | HTML5, CSS3 |
| Parcial 2 | JavaScript (DOM, eventos, `setTimeout` e `setInterval`, `<dialog>`) |
| Parcial 3 | PHP (sem frameworks), MySQL ou MariaDB, sessões PHP |
| Apoio | Git e GitHub, Figma (protótipo), Vercel (publicação) |

---

## 🎨 Créditos e recursos externos

- **Fonte Matcha** (`font/Matcha.ttf`): https://www.dafont.com/matcha-2.font
- **Imagens** (`img/`): imagens geradas no Gemini

---

*Projeto acadêmico desenvolvido para a disciplina Programação para a Web, Faculdade de Tecnologia (FT) da UNICAMP.*
