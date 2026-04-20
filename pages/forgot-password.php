<?php
/**
 * forgot-password.php (view)
 *
 * Camada de apresentação da página de recuperação de senha.
 * Toda a lógica está em forgot-password.logic.php.
 *
 * Variáveis disponíveis na view:
 *  - $error (string)
 *  - $success (string)
 *  - $step (int): controla qual formulário renderizar (1, 2 ou 3)
 */
require_once __DIR__ . '/forgot-password.logic.php';
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
