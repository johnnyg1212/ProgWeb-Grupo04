// jogo.html: toda a dinâmica do Acerte a Toupeira.   
// Funciona só no navegador: não há back-end nem armazenamento de dados.
(function () {
  "use strict";

  /* =====================================================================
     1. REGRAS DO JOGO (para mudar a dificuldade, mexa só aqui)
     ===================================================================== */
  var DURACAO_NIVEL_MS = 20000;       // duração t de cada nível
  var ACERTO_MIN_INICIAL = 70;        // % de acerto exigido no nível 1
  var ACERTO_MIN_PASSO = 2;           // a exigência sobe 2 pontos por nível
  var ACERTO_MIN_LIMITE = 90;         // e nunca passa de 90%
  var TEMPO_ALVO_INICIAL_MS = 1500;   // tempo visível (tt) no nível 1
  var TEMPO_ALVO_PASSO_MS = 150;      // tt diminui 150 ms a cada nível
  var TEMPO_ALVO_MINIMO_MS = 400;     // tt nunca fica menor que isso
  var ESPERA_ENTRE_ALVOS_MS = 400;    // espera base entre alvos (sorteada de 50% a 150%)
  var PROBABILIDADE_BOMBA = 0.3;      // chance de ser bomba (só no modo explosivo)
  var PREPARACAO_MS = 1500;           // tempo de "Prepare-se!" antes do nível 1
  var PAUSA_ENTRE_NIVEIS_MS = 2000;   // pausa depois de passar de nível

  var MIN_BURACOS = 4;
  var MAX_BURACOS = 64;
  var TAMANHO_BURACO_MIN = 36;        // px
  var TAMANHO_BURACO_MAX = 90;        // px
  var ESPACO_ENTRE_BURACOS = 10;      // px (igual ao gap do CSS)
  var NOME_JOGADOR = "Visitante";     // sem login nesta parcial

  /* =====================================================================
     2. ELEMENTOS DA PÁGINA
     ===================================================================== */
  var elTabuleiro = document.getElementById("tabuleiro");
  var elTituloTabuleiro = document.getElementById("titulo-tabuleiro");
  var elModalidade = document.getElementById("info-modalidade");
  var elNivel = document.getElementById("info-nivel");
  var elTempo = document.getElementById("info-tempo");
  var elAcertos = document.getElementById("info-acertos");
  var elPercentual = document.getElementById("info-percentual");
  var elNecessario = document.getElementById("info-necessario");
  var elPontuacao = document.getElementById("info-pontuacao");
  var elAviso = document.getElementById("aviso-jogo");
  var elCorpoHistorico = document.getElementById("corpo-historico");
  var elDialogo = document.getElementById("dialogo-fim");
  var elResumoFim = document.getElementById("resumo-fim");
  var elBotaoNovo = document.getElementById("botao-novo");
  var elDesistir = document.getElementById("botao-desistir");

  /* =====================================================================
     3. ESTADO DO JOGO
     ===================================================================== */
  var config = null;                  // { modo, linhas, colunas }
  var historico = [];                 // partidas desta página (só em memória)

  var estado = {
    fase: "preparando",               // preparando | jogando | entre-niveis | fim
    nivel: 1,
    acertosTotais: 0,                 // toupeiras acertadas na partida inteira
    pontuacaoAcumulada: 0,            // soma dos níveis já encerrados
    nivelContabilizado: false,        // true depois que o nível entrou na pontuação
    fimDoNivel: 0,                    // horário (em ms) em que o nível acaba
    exibidas: 0,                      // toupeiras que já terminaram no nível
    acertosNivel: 0,                  // toupeiras acertadas no nível
    bombasNivel: 0,                   // bombas clicadas no nível
    alvos: [],                        // um item por buraco: null ou { tipo, timeoutId }
    partidaRegistrada: false
  };

  var timers = { relogio: null, aparicao: null, pausa: null };

  /* =====================================================================
     4. CÁLCULOS (regras puras)
     ===================================================================== */
  function tempoDoAlvo(nivel) {
    return Math.max(TEMPO_ALVO_MINIMO_MS, TEMPO_ALVO_INICIAL_MS - TEMPO_ALVO_PASSO_MS * (nivel - 1));
  }

  function acertoNecessario(nivel) {
    return Math.min(ACERTO_MIN_LIMITE, ACERTO_MIN_INICIAL + ACERTO_MIN_PASSO * (nivel - 1));
  }

  // (toupeiras acertadas - bombas clicadas) / toupeiras exibidas, em %
  function porcentagemNivel() {
    if (estado.exibidas === 0) {
      return 0;
    }
    return ((estado.acertosNivel - estado.bombasNivel) / estado.exibidas) * 100;
  }

  function pontuacaoAtual() {
    if (estado.nivelContabilizado) {
      return estado.pontuacaoAcumulada;
    }
    return estado.pontuacaoAcumulada + estado.acertosNivel - estado.bombasNivel;
  }

  function textoModalidade() {
    return config.modo === "classico" ? "Clássico" : "Explosivo";
  }

  function doisDigitos(numero) {
    return (numero < 10 ? "0" : "") + numero;
  }

  function formatarTempo(ms) {
    var segundos = Math.max(0, Math.ceil(ms / 1000));
    return doisDigitos(Math.floor(segundos / 60)) + ":" + doisDigitos(segundos % 60);
  }

  /* =====================================================================
     5. CONFIGURAÇÃO (lida da URL: jogo.html?modo=...&linhas=...&colunas=...)
     ===================================================================== */
  function lerConfiguracao() {
    var parametros = new URLSearchParams(window.location.search);
    var modo = parametros.get("modo");
    var linhas = Number(parametros.get("linhas"));
    var colunas = Number(parametros.get("colunas"));

    var modoValido = modo === "classico" || modo === "explosivo";
    var inteiros = Number.isInteger(linhas) && Number.isInteger(colunas) && linhas >= 1 && colunas >= 1;
    var total = linhas * colunas;

    if (modoValido && inteiros && total >= MIN_BURACOS && total <= MAX_BURACOS) {
      return { modo: modo, linhas: linhas, colunas: colunas };
    }
    return null;
  }

  /* =====================================================================
     6. TABULEIRO
     ===================================================================== */
  function calcularTamanhoBuraco() {
    var larguraUtil = Math.min(window.innerWidth * 0.9, 900) - 110;
    var tamanho = Math.floor((larguraUtil - (config.colunas - 1) * ESPACO_ENTRE_BURACOS) / config.colunas);
    return Math.max(TAMANHO_BURACO_MIN, Math.min(TAMANHO_BURACO_MAX, tamanho));
  }

  function ajustarTamanho() {
    elTabuleiro.style.setProperty("--tam", calcularTamanhoBuraco() + "px");
  }

  function criarTabuleiro() {
    var total = config.linhas * config.colunas;

    elTabuleiro.textContent = "";
    elTabuleiro.style.setProperty("--colunas", config.colunas);
    ajustarTamanho();
    elTituloTabuleiro.textContent = "< Tabuleiro " + config.linhas + " \u00d7 " + config.colunas + " >";

    for (var i = 0; i < total; i++) {
      var buraco = document.createElement("button");
      buraco.type = "button";
      buraco.className = "buraco";
      buraco.dataset.indice = i;
      buraco.setAttribute("aria-label", "Buraco " + (i + 1) + ", vazio");
      elTabuleiro.appendChild(buraco);
    }

    estado.alvos = new Array(total).fill(null);
  }

  function sortearBuracoLivre() {
    var livres = [];
    for (var i = 0; i < estado.alvos.length; i++) {
      if (estado.alvos[i] === null) {
        livres.push(i);
      }
    }
    if (livres.length === 0) {
      return -1;
    }
    return livres[Math.floor(Math.random() * livres.length)];
  }

  function piscar(buraco, classe) {
    buraco.classList.add(classe);
    setTimeout(function () {
      buraco.classList.remove(classe);
    }, 250);
  }

  /* =====================================================================
     7. PAINEL DE INFORMAÇÕES
     ===================================================================== */
  function atualizarTempo(restanteMs) {
    elTempo.textContent = formatarTempo(restanteMs);
  }

  function atualizarPainel() {
    elModalidade.textContent = textoModalidade();
    elNivel.textContent = estado.nivel;
    elAcertos.textContent = estado.acertosTotais;
    elPercentual.textContent = Math.floor(porcentagemNivel()) + "%";
    elNecessario.textContent = "(necessário: " + acertoNecessario(estado.nivel) + "%)";
    elPontuacao.textContent = pontuacaoAtual();
  }

  function avisar(texto) {
    elAviso.textContent = texto;
  }

  /* =====================================================================
     8. ALVOS (toupeiras e bombas)
     ===================================================================== */
  function mostrarAlvo() {
    if (estado.fase !== "jogando") {
      return;
    }
    var indice = sortearBuracoLivre();
    if (indice === -1) {
      return;
    }

    var ehBomba = config.modo === "explosivo" && Math.random() < PROBABILIDADE_BOMBA;
    var tipo = ehBomba ? "bomba" : "toupeira";
    var buraco = elTabuleiro.children[indice];

    buraco.classList.add(ehBomba ? "tem-bomba" : "tem-toupeira");
    buraco.setAttribute("aria-label", "Buraco " + (indice + 1) + ", " + tipo);

    estado.alvos[indice] = {
      tipo: tipo,
      timeoutId: setTimeout(function () {
        esconderAlvoSemAcerto(indice);
      }, tempoDoAlvo(estado.nivel))
    };
  }

  function removerAlvo(indice) {
    var buraco = elTabuleiro.children[indice];
    buraco.classList.remove("tem-toupeira", "tem-bomba");
    buraco.setAttribute("aria-label", "Buraco " + (indice + 1) + ", vazio");
    estado.alvos[indice] = null;
  }

  function removerTodosAlvos() {
    for (var i = 0; i < estado.alvos.length; i++) {
      if (estado.alvos[i] !== null) {
        clearTimeout(estado.alvos[i].timeoutId);
        removerAlvo(i);
      }
    }
  }

  // O alvo acabou seu tempo sem ser clicado.
  function esconderAlvoSemAcerto(indice) {
    var alvo = estado.alvos[indice];
    if (alvo === null || estado.fase !== "jogando") {
      return;
    }
    removerAlvo(indice);
    if (alvo.tipo === "toupeira") {
      estado.exibidas++;          // toupeira que escapou conta como exibida
    }
    atualizarPainel();
    agendarProximaAparicao();
  }

  // Um alvo por vez: o próximo só é agendado quando o atual termina.
  // Não agenda se o alvo não couber inteiro dentro do nível.
  function agendarProximaAparicao() {
    clearTimeout(timers.aparicao);
    if (estado.fase !== "jogando") {
      return;
    }
    var espera = ESPERA_ENTRE_ALVOS_MS * (0.5 + Math.random());
    var restante = estado.fimDoNivel - Date.now();
    if (restante - espera < tempoDoAlvo(estado.nivel)) {
      return;
    }
    timers.aparicao = setTimeout(mostrarAlvo, espera);
  }

  /* =====================================================================
     9. CLIQUES NO TABULEIRO (um único ouvinte para todos os buracos)
     ===================================================================== */
  function tratarClique(evento) {
    var buraco = evento.target.closest(".buraco");
    if (buraco === null || estado.fase !== "jogando") {
      return;
    }
    if (Date.now() >= estado.fimDoNivel) {
      return;                     // o tempo do nível já acabou
    }

    var indice = Number(buraco.dataset.indice);
    var alvo = estado.alvos[indice];
    if (alvo === null) {
      return;                     // buraco vazio: nada acontece
    }

    clearTimeout(alvo.timeoutId);
    removerAlvo(indice);

    if (alvo.tipo === "toupeira") {
      estado.acertosNivel++;
      estado.acertosTotais++;
      estado.exibidas++;
      piscar(buraco, "acerto");
    } else {
      estado.bombasNivel++;
      piscar(buraco, "erro-bomba");
    }

    atualizarPainel();
    agendarProximaAparicao();
  }

  /* =====================================================================
     10. NÍVEIS E FIM DE PARTIDA
     ===================================================================== */
  function limparTimers() {
    clearInterval(timers.relogio);
    clearTimeout(timers.aparicao);
    clearTimeout(timers.pausa);
    timers.relogio = null;
    timers.aparicao = null;
    timers.pausa = null;
  }

  function iniciarPartida() {
    limparTimers();
    removerTodosAlvos();
    estado.fase = "preparando";
    estado.nivel = 1;
    estado.acertosTotais = 0;
    estado.pontuacaoAcumulada = 0;
    estado.nivelContabilizado = false;
    estado.exibidas = 0;
    estado.acertosNivel = 0;
    estado.bombasNivel = 0;
    estado.partidaRegistrada = false;

    atualizarTempo(DURACAO_NIVEL_MS);
    atualizarPainel();
    avisar("Prepare-se!");
    timers.pausa = setTimeout(iniciarNivel, PREPARACAO_MS);
  }

  function iniciarNivel() {
    estado.fase = "jogando";
    estado.nivelContabilizado = false;
    estado.exibidas = 0;
    estado.acertosNivel = 0;
    estado.bombasNivel = 0;
    estado.fimDoNivel = Date.now() + DURACAO_NIVEL_MS;

    var aviso = "Nível " + estado.nivel + ": acerte pelo menos " + acertoNecessario(estado.nivel) + "% das toupeiras.";
    if (config.modo === "explosivo") {
      aviso += " Cuidado com as bombas!";
    }
    avisar(aviso);

    atualizarTempo(DURACAO_NIVEL_MS);
    atualizarPainel();

    timers.relogio = setInterval(tiqueDoRelogio, 100);
    agendarProximaAparicao();
  }

  // Roda a cada 100 ms. O tempo restante vem do relógio do computador
  // (e não de contar tiques), porque setInterval pode atrasar.
  function tiqueDoRelogio() {
    var restante = estado.fimDoNivel - Date.now();
    if (restante <= 0) {
      encerrarNivel();
      return;
    }
    atualizarTempo(restante);
  }

  // Aqui o jogo decide se o jogador passa de nível ou se a partida acaba.
  function encerrarNivel() {
    limparTimers();
    removerTodosAlvos();           // alvo ainda visível (atraso de timer) não conta
    atualizarTempo(0);

    var porcentagem = porcentagemNivel();
    var necessario = acertoNecessario(estado.nivel);

    estado.pontuacaoAcumulada += estado.acertosNivel - estado.bombasNivel;
    estado.nivelContabilizado = true;
    atualizarPainel();

    if (porcentagem >= necessario) {
      estado.fase = "entre-niveis";
      avisar("Nível " + estado.nivel + " concluído com " + Math.floor(porcentagem) +
        "%! Próximo: nível " + (estado.nivel + 1) + " (meta " + acertoNecessario(estado.nivel + 1) + "%).");
      timers.pausa = setTimeout(function () {
        estado.nivel++;
        iniciarNivel();
      }, PAUSA_ENTRE_NIVEIS_MS);
    } else {
      finalizarPartida(porcentagem, necessario);
    }
  }

  function finalizarPartida(porcentagem, necessario) {
    estado.fase = "fim";
    registrarPartida();

    elResumoFim.textContent = "Você fez " + Math.floor(porcentagem) + "% no nível " + estado.nivel +
      " (o mínimo era " + necessario + "%). Pontuação final: " + pontuacaoAtual() + ".";
    avisar("Fim de jogo.");

    if (typeof elDialogo.showModal === "function") {
      elDialogo.showModal();
    } else {
      elDialogo.setAttribute("open", "");
    }
  }

  /* =====================================================================
     11. HISTÓRICO (fica só na memória desta página)
     ===================================================================== */
  function registrarPartida() {
    if (estado.partidaRegistrada) {
      return;
    }
    estado.partidaRegistrada = true;

    var partida = {
      jogador: NOME_JOGADOR,
      linhas: config.linhas,
      colunas: config.colunas,
      modalidade: textoModalidade(),
      pontuacao: pontuacaoAtual(),
      nivel: estado.nivel,
      quando: new Date()
    };
    historico.unshift(partida);
    desenharHistorico();
  }

  function criarLinhaHistorico(partida) {
    var linha = document.createElement("tr");
    var textos = [
      partida.jogador,
      partida.linhas + " \u00d7 " + partida.colunas,
      partida.modalidade,
      partida.pontuacao,
      partida.nivel,
      partida.quando.toLocaleString("pt-BR", { dateStyle: "short", timeStyle: "short" })
    ];
    textos.forEach(function (texto) {
      var celula = document.createElement("td");
      celula.textContent = texto;
      linha.appendChild(celula);
    });
    return linha;
  }

  function desenharHistorico() {
    elCorpoHistorico.textContent = "";
    historico.forEach(function (partida) {
      elCorpoHistorico.appendChild(criarLinhaHistorico(partida));
    });
  }

  /* =====================================================================
     12. DESISTIR, JOGAR DE NOVO E INÍCIO
     ===================================================================== */
  // O clique em "Desistir" registra a partida e o link segue para as configurações.
  elDesistir.addEventListener("click", function () {
    registrarPartida();
    limparTimers();
    removerTodosAlvos();
  });

  elBotaoNovo.addEventListener("click", function () {
    elDialogo.close();
    iniciarPartida();
  });

  // Esc não pode fechar a janela de fim de partida: o jogador precisa escolher uma opção.
  elDialogo.addEventListener("cancel", function (evento) {
    evento.preventDefault();
  });

  elTabuleiro.addEventListener("click", tratarClique);
  window.addEventListener("resize", function () {
    if (config !== null) {
      ajustarTamanho();
    }
  });
  window.addEventListener("pagehide", limparTimers);

  config = lerConfiguracao();
  if (config === null) {
    // Endereço sem configurações válidas: volta para a tela de configuração.
    window.location.replace("configJogo.html");
  } else {
    var imagemToupeira = new Image();   // carrega a imagem antes da primeira aparição
    imagemToupeira.src = "img/mascoto_jogo.png";
    criarTabuleiro();
    iniciarPartida();
  }
})();