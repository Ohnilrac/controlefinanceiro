<?php
/**
 * register.php (view)
 *
 * Camada de apresentação da página de cadastro.
 * Toda a lógica (sessão, validação, banco) está em register.logic.php.
 *
 * Variáveis disponíveis para uso na view:
 *  - $error (string): erro de validação ou sistema
 *  - $success (string): mensagem de sucesso após cadastro
 *  - $error_fields (array): nomes dos campos a destacar como erro
 */
require_once __DIR__ . '/register.logic.php';
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
