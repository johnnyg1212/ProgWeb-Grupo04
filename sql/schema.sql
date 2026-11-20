-- Jogo da Toupeira: esquema do banco de dados (MySQL 5.7+ / MariaDB 10.3+)
CREATE DATABASE IF NOT EXISTS toupeira
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE toupeira;

CREATE TABLE IF NOT EXISTS usuarios (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome        VARCHAR(100) NOT NULL,
  nascimento  DATE         NOT NULL,
  cpf         CHAR(11)     NOT NULL,
  telefone    VARCHAR(11)  NOT NULL,
  email       VARCHAR(120) NOT NULL,
  username    VARCHAR(20)  NOT NULL,
  senha_hash  VARCHAR(255) NOT NULL,
  criado_em   DATETIME     NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_cpf (cpf),
  UNIQUE KEY uq_usuarios_email (email),
  UNIQUE KEY uq_usuarios_username (username)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS partidas (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id  INT UNSIGNED NOT NULL,
  modo        ENUM('classico','explosivo') NOT NULL,
  linhas      TINYINT UNSIGNED NOT NULL,
  colunas     TINYINT UNSIGNED NOT NULL,
  pontuacao   INT NOT NULL,
  nivel       SMALLINT UNSIGNED NOT NULL,
  jogada_em   DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_partidas_usuario (usuario_id, jogada_em),
  KEY idx_partidas_ranking (modo, pontuacao, nivel),
  CONSTRAINT fk_partidas_usuario FOREIGN KEY (usuario_id)
    REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB;
