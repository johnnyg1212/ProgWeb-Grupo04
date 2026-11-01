// configJogo.html: confere o tamanho do tabuleiro antes de abrir a partida.
(function () {
  "use strict";

  var MIN_BURACOS = 4;
  var MAX_BURACOS = 64;

  var formulario = document.querySelector(".formulario-config");
  var campoLinhas = document.getElementById("linhas");
  var campoColunas = document.getElementById("colunas");
  var mensagemErro = document.getElementById("erro-config");

  function mostrarErro(texto) {
    mensagemErro.textContent = texto;
    mensagemErro.hidden = false;
  }

  function esconderErro() {
    mensagemErro.textContent = "";
    mensagemErro.hidden = true;
  }

  formulario.addEventListener("submit", function (evento) {
    var linhas = Number(campoLinhas.value);
    var colunas = Number(campoColunas.value);

    if (!Number.isInteger(linhas) || !Number.isInteger(colunas) || linhas < 1 || colunas < 1) {
      evento.preventDefault();
      mostrarErro("Informe números inteiros positivos para linhas e colunas.");
      return;
    }

    var total = linhas * colunas;
    if (total < MIN_BURACOS || total > MAX_BURACOS) {
      evento.preventDefault();
      mostrarErro(
        "O tabuleiro precisa ter entre " + MIN_BURACOS + " e " + MAX_BURACOS +
        " buracos no total (linhas \u00d7 colunas). Você informou " + total + "."
      );
      return;
    }

    esconderErro();
  });

  // Some com o aviso assim que o jogador corrige um dos campos.
  campoLinhas.addEventListener("input", esconderErro);
  campoColunas.addEventListener("input", esconderErro);
})();