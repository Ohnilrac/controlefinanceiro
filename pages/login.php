<?php 
session_start();
require_once '../includes/db.php';

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
    $stmt->execute(['login' => $login]);
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