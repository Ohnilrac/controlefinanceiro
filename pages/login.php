<?php
/**
 * login.php (view)
 *
 * Camada de apresentação da página de login.
 * Toda a lógica (sessão, banco, processamento do POST)
 * está em login.logic.php, que é incluído logo abaixo.
 *
 * Variáveis disponíveis para uso na view:
 *  - $error (string): mensagem de erro do formulário, se houver.
 */
require_once __DIR__ . '/login.logic.php';
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
