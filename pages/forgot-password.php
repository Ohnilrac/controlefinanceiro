<?php 
session_start();
require_once '../includes/db.php';
require_once '../includes/PHPMailer/PHPMailer.php';
require_once '../includes/PHPMailer/SMTP.php';
require_once '../includes/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Verifica se o usuário já está logado
if (isset($_SESSION['user_id'])) {     
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';
$step = isset($_GET['step']) ? $_GET['step'] : 1;


//envia email de recuperação de senha
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
    //str_pad é uma função que preenche uma string até um determinado comprimento com um caractere específico, neste caso, preenche com zeros à esquerda para garantir que o token tenha 6 dígitos
    //STR_PAD_LEFT é uma constante que indica que o preenchimento deve ser feito à esquerda da string
    $token = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    //strtotime é uma função que converte uma string de data/hora em um timestamp Unix, neste caso, adiciona 15 minutos ao horário atual para definir a expiração do token
    $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    $stmt = $pdo->prepare('DELETE FROM password_resets WHERE email = :email');
    $stmt->execute([':email' => $email]);

    $stmt = $pdo->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)');
    $stmt->execute([':email' => $email, ':token' => $token, ':expires_at' => $expires_at]);

    try {
      $mail = new PHPMailer(true);
      $mail->isSMTP();
      $mail->Host = MAIL_HOST;
      $mail->SMTPAuth = true;
      $mail->Username = MAIL_USER;
      $mail->Password = MAIL_PASS;
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      $mail->Port = MAIL_PORT;
      $mail->CharSet = 'UTF-8';

      $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
      $mail->addAddress($email);
      $mail->Subject = 'Recuperação de Senha - Controle Financeiro';
      $mail->Body = "Olá,\n\nVocê solicitou a recuperação de senha para sua conta no Controle Financeiro. Use o código abaixo para redefinir sua senha:\n\nCódigo: $token\n\nEste código é válido por 15 minutos.\n\nSe você não solicitou esta recuperação, por favor ignore este email.\n\nAtenciosamente,\nEquipe Controle Financeiro";
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


// verifica o token e permite redefinir a senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step == 2) {
  $token = trim($_POST['token']);
  $password = trim($_POST['password']);
  $confirm_password = trim($_POST['confirm_password']);
  $email = $_SESSION['reset_email'];

  if (empty($token) || empty($password) || empty($confirm_password)) {
    $error = 'Por favor, preencha todos os campos.';
  } elseif ($password !== $confirm_password) {
    $error = 'As senhas não coincidem.';
  } elseif (strlen($password) < 6) {
    $error = 'A senha deve ter pelo menos 6 caracteres.';
  } else {
    $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE email = :email AND token = :token AND expires_at > NOW()');
    $stmt->execute([':email' => $email, ':token' => $token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
      $error = 'Token inválido ou expirado.';
    } else {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare('UPDATE users SET password = :password WHERE email = :email');
      if ($stmt->execute([':password' => $hashed_password, ':email' => $email])) {
        // Limpa o token após a redefinição da senha
        $stmt = $pdo->prepare('DELETE FROM password_resets WHERE email = :email');
        $stmt->execute([':email' => $email]);

        // unset é uma função que destrói a variável especificada, neste caso, remove o email da sessão para evitar que seja reutilizado
        unset($_SESSION['reset_email']);
        $success = 'Senha redefinida com sucesso! Você pode fazer login agora. <a href="login.php">Faça login</a>';
        $step = 3; 
      } else {
        $error = 'Ocorreu um erro ao redefinir a senha. Por favor, tente novamente.';
      }
    }
  }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Controle Financeiro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">

    <div class="auth-container">
        <div class="auth-card">

            <div class="auth-header">
                <div class="auth-icon">💰</div>
                <h1>Controle Financeiro</h1>
                <?php if ($step == 1): ?>
                    <p>Informe seu email para recuperar a senha</p>
                <?php elseif ($step == 2): ?>
                    <p>Digite o código enviado para seu email</p>
                <?php else: ?>
                    <p>Senha redefinida com sucesso!</p>
                <?php endif; ?>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <form action="?step=1" method="POST">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Digite seu email">
                    </div>
                    <button type="submit" class="btn-primary">Enviar código</button>
                </form>

            <?php elseif ($step == 2): ?>
                <form action="?step=2" method="POST">
                    <div class="form-group">
                        <label for="token">Código de verificação</label>
                        <input type="text" id="token" name="token" placeholder="Digite o código de 6 dígitos" maxlength="6">
                    </div>
                    <div class="form-group">
                        <label for="password">Nova senha</label>
                        <input type="password" id="password" name="password" placeholder="Digite sua nova senha">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmar nova senha</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirme sua nova senha">
                    </div>
                    <button type="submit" class="btn-primary">Redefinir senha</button>
                </form>

            <?php endif; ?>

            <p class="auth-link"><a href="login.php">Voltar ao login</a></p>

        </div>
    </div>

</body>
</html>