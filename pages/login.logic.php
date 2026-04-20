<?php
/**
 * login.logic.php
 *
 * Camada de lógica da página de login.
 *
 * Responsabilidades:
 *  - Iniciar a sessão
 *  - Incluir a conexão com o banco (PDO)
 *  - Redirecionar o usuário caso já esteja autenticado
 *  - Processar o envio do formulário (POST) e autenticar o usuário
 *
 * Variáveis exportadas para a view (login.php):
 *  - $error (string): mensagem de erro a ser exibida no formulário
 *
 * IMPORTANTE: este arquivo NÃO deve conter HTML. Qualquer saída
 * (echo, print, espaços antes da tag <?php) quebraria o header()
 * usado nos redirecionamentos abaixo.
 */

// session_start() precisa ser chamado ANTES de qualquer saída ao navegador
session_start();

// require_once carrega o arquivo apenas uma vez na execução, evitando
// redeclaração de funções e constantes caso o mesmo arquivo seja
// incluído mais de uma vez na mesma requisição.
require_once __DIR__ . '/../includes/db.php';

// isset() verifica se a variável existe e não é nula.
// Se o usuário já tem sessão ativa, já mandamos direto para o dashboard.
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Inicializa a variável que será lida pela view.
// Deixamos como string vazia para simplificar o if (!empty($error)) no HTML.
$error = '';

// Só entra no bloco de processamento quando a requisição for via POST
// (ou seja, quando o formulário for de fato enviado).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // trim() remove espaços em branco do início e do final da string.
    // Evita que um espaço acidental digitado pelo usuário quebre o login.
    $login    = trim($_POST['login']);
    $password = trim($_POST['password']);

    if (empty($login) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        // Prepared statement: separa o SQL dos valores,
        // protegendo contra SQL Injection.
        $stmt = $pdo->prepare(
            'SELECT * FROM users WHERE email = :login OR username = :login'
        );
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // password_verify() compara a senha digitada com o hash salvo no banco.
        // É a função correta para quem armazena senhas com password_hash().
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Login ou senha incorretos.';
        }
    }
}
