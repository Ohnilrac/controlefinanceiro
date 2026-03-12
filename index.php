<?php
require_once 'includes/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
        DB_USER,
        DB_PASS
    );
    echo "Conexão bem sucedida!";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>