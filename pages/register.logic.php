<?php
/**
 * register.logic.php
 *
 * Camada de lógica da página de cadastro de novos usuários.
 *
 * Responsabilidades:
 *  - Iniciar a sessão
 *  - Incluir conexão PDO
 *  - Redirecionar usuário já autenticado para o dashboard
 *  - Validar os dados do formulário (campos vazios, email válido, senhas iguais)
 *  - Verificar duplicidade de email/username no banco
 *  - Inserir o novo usuário com a senha hasheada
 *
 * Variáveis exportadas para a view (register.php):
 *  - $error (string): mensagem de erro a ser exibida no topo
 *  - $success (string): mensagem de sucesso após cadastro
 *  - $error_fields (array): nomes dos campos a serem destacados como erro
 */

session_start();
require_once __DIR__ . '/../includes/db.php';

// isset() verifica se a variável existe e não é nula.
// Se já está logado, vai direto para o dashboard.
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error        = '';
$success      = '';
$error_fields = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // trim() remove espaços em branco do início e final da string
    $full_name        = trim($_POST['full_name']);
    $email            = trim($_POST['email']);
    $username         = trim($_POST['username']);
    $password         = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($full_name) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        $error = 'Por favor, preencha todos os campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // filter_var() com FILTER_VALIDATE_EMAIL valida o formato do e-mail
        $error = 'Por favor, insira um email válido.';
    } elseif ($password !== $confirm_password) {
        $error = 'As senhas não coincidem.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email OR username = :username');
        $stmt->execute([':email' => $email, ':username' => $username]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Identifica especificamente qual campo está duplicado
            $stmt_email = $pdo->prepare('SELECT id FROM users WHERE email = :email');
            $stmt_email->execute([':email' => $email]);
            $stmt_username = $pdo->prepare('SELECT id FROM users WHERE username = :username');
            $stmt_username->execute([':username' => $username]);

            $email_exists    = $stmt_email->fetch();
            $username_exists = $stmt_username->fetch();

            if ($email_exists && $username_exists) {
                $error        = 'Email e nome de usuário já estão em uso.';
                $error_fields = ['email', 'username'];
            } elseif ($email_exists) {
                $error        = 'Este email já está em uso.';
                $error_fields = ['email'];
            } else {
                $error        = 'Este nome de usuário já está em uso.';
                $error_fields = ['username'];
            }
        } else {
            // PASSWORD_DEFAULT é uma constante que indica o algoritmo de hash em uso.
            // Permite atualizações futuras do algoritmo sem alterar o código.
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (full_name, email, username, password) VALUES (:full_name, :email, :username, :password)');

            if ($stmt->execute([
                ':full_name' => $full_name,
                ':email'     => $email,
                ':username'  => $username,
                ':password'  => $hashed_password,
            ])) {
                $success = 'Registro bem-sucedido! Você pode fazer login agora.';
            } else {
                $error = 'Ocorreu um erro ao registrar. Por favor, tente novamente.';
            }
        }
    }
}
