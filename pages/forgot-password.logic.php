<?php
/**
 * forgot-password.logic.php
 *
 * Camada de lógica da página de recuperação de senha.
 *
 * O fluxo possui 3 etapas (controladas pela variável $step):
 *  1. Usuário informa o e-mail e recebe um código de 6 dígitos por e-mail
 *  2. Usuário informa o código + nova senha
 *  3. Senha redefinida com sucesso (mensagem final)
 *
 * Responsabilidades:
 *  - Iniciar sessão
 *  - Incluir PDO e PHPMailer
 *  - Redirecionar usuário já autenticado
 *  - Gerar token + expiração (15 min) e enviar por SMTP
 *  - Validar token e redefinir a senha do usuário
 *
 * Variáveis exportadas para a view (forgot-password.php):
 *  - $error (string)
 *  - $success (string)
 *  - $step (int): etapa atual do fluxo (1, 2 ou 3)
 */

session_start();
require_once '../includes/db.php';
require_once '../includes/PHPMailer/PHPMailer.php';
require_once '../includes/PHPMailer/SMTP.php';
require_once '../includes/PHPMailer/Exception.php';

// O "use" importa as classes do namespace do PHPMailer para o escopo atual,
// permitindo escrever PHPMailer em vez de PHPMailer\PHPMailer\PHPMailer.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Se o usuário já está logado, vai direto para o dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error   = '';
$success = '';
// O step define qual formulário será exibido (vem por GET na URL)
$step    = isset($_GET['step']) ? $_GET['step'] : 1;


// ETAPA 1: Envia o e-mail com o código de recuperação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step == 1) {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error = 'Por favor, insira seu email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor, insira um email válido.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = 'Nenhuma conta encontrada com esse email.';
        } else {
            // str_pad() preenche a string até o tamanho indicado.
            // STR_PAD_LEFT preenche pela esquerda — garante token sempre com 6 dígitos.
            $token = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            // strtotime() converte texto em timestamp Unix.
            // Aqui calculamos a expiração 15 minutos no futuro.
            $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            // Apaga tokens antigos do mesmo e-mail antes de criar um novo,
            // evitando múltiplos códigos válidos para a mesma conta.
            $stmt = $pdo->prepare('DELETE FROM password_resets WHERE email = :email');
            $stmt->execute([':email' => $email]);

            $stmt = $pdo->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)');
            $stmt->execute([
                ':email'      => $email,
                ':token'      => $token,
                ':expires_at' => $expires_at,
            ]);

            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = MAIL_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = MAIL_USER;
                $mail->Password   = MAIL_PASS;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = MAIL_PORT;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
                $mail->addAddress($email);
                $mail->Subject = 'Recuperação de Senha - Controle Financeiro';
                $mail->Body    = "Olá,\n\nVocê solicitou a recuperação de senha para sua conta no Controle Financeiro. Use o código abaixo para redefinir sua senha:\n\nCódigo: $token\n\nEste código é válido por 15 minutos.\n\nSe você não solicitou esta recuperação, por favor ignore este email.\n\nAtenciosamente,\nEquipe Controle Financeiro";
                $mail->isHTML(true);

                $mail->send();
                $_SESSION['reset_email'] = $email;
                header('Location: forgot-password.php?step=2');
                exit();
            } catch (Exception $e) {
                $error = 'Ocorreu um erro ao enviar o email de recuperação de senha. Por favor, tente novamente.';
            }
        }
    }
}


// ETAPA 2: Verifica o token e permite redefinir a senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step == 2) {
    $token            = trim($_POST['token']);
    $password         = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $email            = $_SESSION['reset_email'];

    if (empty($token) || empty($password) || empty($confirm_password)) {
        $error = 'Por favor, preencha todos os campos.';
    } elseif ($password !== $confirm_password) {
        $error = 'As senhas não coincidem.';
    } elseif (strlen($password) < 6) {
        $error = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        // expires_at > NOW() garante que o token ainda não expirou
        $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE email = :email AND token = :token AND expires_at > NOW()');
        $stmt->execute([':email' => $email, ':token' => $token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reset) {
            $error = 'Token inválido ou expirado.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password = :password WHERE email = :email');

            if ($stmt->execute([':password' => $hashed_password, ':email' => $email])) {
                // Limpa o token após o uso para impedir reutilização
                $stmt = $pdo->prepare('DELETE FROM password_resets WHERE email = :email');
                $stmt->execute([':email' => $email]);

                // unset() destrói a variável da sessão para evitar reuso
                unset($_SESSION['reset_email']);
                $success = 'Senha redefinida com sucesso! Você pode fazer login agora. <a href="login.php">Faça login</a>';
                $step    = 3;
            } else {
                $error = 'Ocorreu um erro ao redefinir a senha. Por favor, tente novamente.';
            }
        }
    }
}
