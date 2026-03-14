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
$error_fields = [];

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
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existing) {
        // Verifica qual campo está duplicado
        $stmt_email = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $stmt_email->execute([':email' => $email]);
        $stmt_username = $pdo->prepare('SELECT id FROM users WHERE username = :username');
        $stmt_username->execute([':username' => $username]);
        
        $email_exists = $stmt_email->fetch();
        $username_exists = $stmt_username->fetch();
        
        if ($email_exists && $username_exists) {
            $error = 'Email e nome de usuário já estão em uso.';
            $error_fields = ['email', 'username'];
        } elseif ($email_exists) {
            $error = 'Este email já está em uso.';
            $error_fields = ['email'];
        } else {
            $error = 'Este nome de usuário já está em uso.';
            $error_fields = ['username'];
        }
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
                <a href="login.php" class="btn-primary" style="text-align: center; display: block; text-decoration: none; margin-top: 8px;">Fazer login</a>
            <?php else: ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="full_name">Nome completo</label>
                    <input type="text" id="full_name" name="full_name" placeholder="Digite seu nome completo" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="username">Nome de usuário</label>
                    <input type="text" id="username" name="username" class="<?php echo in_array('username', $error_fields) ? 'input-error' : ''; ?>" placeholder="Digite seu nome de usuário" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="<?php echo in_array('email', $error_fields) ? 'input-error' : ''; ?>" placeholder = "Digite seu email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
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
            <?php endif; ?>
            <p class="auth-link">Já tem uma conta? <a href="login.php">Faça login</a></p>

        </div>
    </div>

</body>
</html>