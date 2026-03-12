<?php 
session_start();
require_once __DIR__ . '/../includes/db.php';

//isset é uma função que verifica se a variável existe e não é nula
if (isset($_SESSION['user_id'])) {     
    header('Location: dashboard.php');
    exit();
}


$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  //trim é uma função que remove os espaços em branco do início e do final de uma string
  $login = trim($_POST['login']);
  $password = trim($_POST['password']);

  if (empty($login) || empty($password)) {
    $error = 'Por favor, preencha todos os campos.';
  } else {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :login OR username = :login');
    $stmt->execute([':login' => $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    //password_verify é uma função que verifica se a senha fornecida corresponde ao hash armazenado no banco de dados
    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_name'] = $user['full_name'];
      header('Location: dashboard.php');
      exit();
    } else {
      $error = 'Login ou senha incorretos.';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Controle Financeiro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">

    <div class="auth-container">
        <div class="auth-card">

            <div class="auth-header">
                <div class="auth-icon">💰</div>
                <h1>Controle Financeiro</h1>
                <p>Acesse sua conta para continuar</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="login">Email ou usuário</label>
                    <input type="text" id="login" name="login" placeholder="Digite seu email ou usuário">
                </div>

                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" placeholder="Digite sua senha">
                    <a href="forgot-password.php" class="forgot-link">Esqueceu a senha?</a>
                </div>

                <button type="submit" class="btn-primary">Entrar</button>
            </form>

            <p class="auth-link">Não tem uma conta? <a href="register.php">Cadastre-se</a></p>

        </div>
    </div>

</body>
</html>