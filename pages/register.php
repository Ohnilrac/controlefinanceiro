<?php 
session_start();
require_once __DIR__ . '/../includes/db.php';


//isset é uma função que verifica se a variável existe e não é nula
if (isset($_SESSION['user_id'])) {     
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  //trim é uma função que remove os espaços em branco do início e do final de uma string
  $full_name = trim($_POST['full_name']);
  $email = trim($_POST['email']);
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);
  $confirm_password = trim($_POST['confirm_password']);

  if (empty($full_name) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
    $error = 'Por favor, preencha todos os campos.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //filter_var é uma função que filtra uma variável com um filtro específico, neste caso, valida se o email é válido
    $error = 'Por favor, insira um email válido.';
  } elseif ($password !== $confirm_password) {
    $error = 'As senhas não coincidem.';
  } else {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email OR username = :username');
    $stmt->execute([':email' => $email, ':username' => $username]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
      $error = 'Email ou nome de usuário já estão em uso.';
    } else {
      // PASSWORD_DEFAULT é uma constante que indica o algoritmo de hash a ser usado, atualmente é o bcrypt, e pode ser atualizado no futuro para um algoritmo mais forte sem precisar alterar o código
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare('INSERT INTO users (full_name, email, username, password) VALUES (:full_name, :email, :username, :password)');
      if ($stmt->execute([':full_name' => $full_name, ':email' => $email, ':username' => $username, ':password' => $hashed_password])) {
        $success = 'Registro bem-sucedido! Você pode fazer login agora.';
      } else {
        $error = 'Ocorreu um erro ao registrar. Por favor, tente novamente.';
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
    <title>Cadastro - Controle Financeiro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">

    <div class="auth-container">
        <div class="auth-card">

            <div class="auth-header">
                <div class="auth-icon">💰</div>
                <h1>Controle Financeiro</h1>
                <p>Crie sua conta gratuitamente</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="full_name">Nome completo</label>
                    <input type="text" id="full_name" name="full_name" placeholder="Digite seu nome completo">
                </div>

                <div class="form-group">
                    <label for="username">Nome de usuário</label>
                    <input type="text" id="username" name="username" placeholder="Digite seu nome de usuário">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Digite seu email">
                </div>

                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" placeholder="Digite sua senha">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmar senha</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirme sua senha">
                </div>

                <button type="submit" class="btn-primary">Cadastrar</button>
            </form>

            <p class="auth-link">Já tem uma conta? <a href="login.php">Faça login</a></p>

        </div>
    </div>

</body>
</html>