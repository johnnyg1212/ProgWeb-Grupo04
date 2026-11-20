<?php
// Configurações do banco de dados MySQL/MariaDB.
// Ajuste os valores abaixo para o seu ambiente (XAMPP padrão: root, sem senha).
const DB_HOST = 'localhost';
const DB_PORT = 3306;
const DB_NAME = 'toupeira';
const DB_USER = 'root';
const DB_PASS = '#Toupeira04';

// Regras do tabuleiro (as mesmas de js/configurar.js e js/jogo.js)
const MIN_BURACOS = 4;
const MAX_BURACOS = 64;

date_default_timezone_set('America/Sao_Paulo');
