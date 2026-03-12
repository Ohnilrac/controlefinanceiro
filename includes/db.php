<?php 

require_once 'config.php';

try {
    $pdo = new PDO('mysql:dbname='.DB_NAME.';host='.DB_HOST, DB_USER, DB_PASS . ';charset=utf8');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die( "Erro ao conectar com o banco de dados: " . $e->getMessage());
}


?>