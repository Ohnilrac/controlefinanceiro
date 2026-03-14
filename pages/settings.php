<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Busca dados do usuário
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Busca categorias para o orçamento
$stmt = $pdo->prepare('
    SELECT c.*, COALESCE(SUM(e.amount), 0) as total_spent
    FROM categories c
    LEFT JOIN expenses e ON c.id = e.category_id
    WHERE c.user_id = :user_id
    GROUP BY c.id
    ORDER BY c.name
');
$stmt->execute([':user_id' => $user_id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ATUALIZAR PERFIL E SEGURANÇA
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $current_password = trim($_POST['current_password'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        if (empty($full_name) || empty($username) || empty($email)) {
            $error = 'Por favor, preencha todos os campos obrigatórios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Por favor, insira um email válido.';
        } else {
            // Verifica se username ou email já existem para outro usuário
            $stmt = $pdo->prepare('SELECT id FROM users WHERE (email = :email OR username = :username) AND id != :id');
            $stmt->execute([':email' => $email, ':username' => $username, ':id' => $user_id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $error = 'Email ou nome de usuário já está em uso por outro usuário.';
            } else {
                // Atualiza perfil
                $stmt = $pdo->prepare('UPDATE users SET full_name = :full_name, username = :username, email = :email WHERE id = :id');
                $stmt->execute([':full_name' => $full_name, ':username' => $username, ':email' => $email, ':id' => $user_id]);

                // Atualiza senha se preenchida
                if (!empty($current_password) && !empty($new_password)) {
                    if (!password_verify($current_password, $user['password'])) {
                        $error = 'Senha atual incorreta.';
                    } elseif ($new_password !== $confirm_password) {
                        $error = 'As novas senhas não coincidem.';
                    } elseif (strlen($new_password) < 6) {
                        $error = 'A nova senha deve ter no mínimo 6 caracteres.';
                    } else {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
                        $stmt->execute([':password' => $hashed_password, ':id' => $user_id]);
                        $success = 'Perfil e senha atualizados com sucesso!';
                    }
                } else {
                    $success = 'Perfil atualizado com sucesso!';
                }

                // Atualiza nome na sessão
                $_SESSION['user_name'] = $full_name;

                // Recarrega dados do usuário
                $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
                $stmt->execute([':id' => $user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
    }

    // ATUALIZAR SALÁRIO
    if (isset($_POST['update_salary'])) {
        $salary = $_POST['salary'] ?? 0;
        $stmt = $pdo->prepare('UPDATE users SET salary = :salary WHERE id = :id');
        $stmt->execute([':salary' => $salary, ':id' => $user_id]);
        $success = 'Salário atualizado com sucesso!';

        // Recarrega dados
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ATUALIZAR ORÇAMENTO POR CATEGORIA
    if (isset($_POST['update_budget'])) {
        $budgets = $_POST['budget'] ?? [];
        foreach ($budgets as $category_id => $budget_value) {
            $budget_value = str_replace(['R$ ', '.'], '', $budget_value);
            $budget_value = str_replace(',', '.', $budget_value);
            $budget_value = floatval($budget_value);
            $stmt = $pdo->prepare('UPDATE categories SET budget = :budget WHERE id = :id AND user_id = :user_id');
            $stmt->execute([':budget' => $budget_value, ':id' => $category_id, ':user_id' => $user_id]);
        }
        $success = 'Orçamentos atualizados com sucesso!';
        
        // Recarrega categorias
        $stmt = $pdo->prepare('
    SELECT c.*, COALESCE(SUM(e.amount), 0) as total_spent
    FROM categories c
    LEFT JOIN expenses e ON c.id = e.category_id
    WHERE c.user_id = :user_id
    GROUP BY c.id
    ORDER BY c.name
');
$stmt->execute([':user_id' => $user_id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Controle Financeiro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="dashboard-page">

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>
<div class="topbar">
    <button class="hamburger" onclick="openSidebar()">
        <span></span>
        <span></span>
        <span></span>
    </button>
</div>

<div class="content-wrapper">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-icon">💰</span>
            <span class="logo-text">Controle<br>Financeiro</span>
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">
                <span class="nav-icon">🏠</span>
                <span>Dashboard</span>
            </a>
            <a href="expenses.php" class="nav-item">
                <span class="nav-icon">📋</span>
                <span>Meus Gastos</span>
            </a>
            <a href="categories.php" class="nav-item">
                <span class="nav-icon">🏷️</span>
                <span>Categorias</span>
            </a>

            <div class="nav-divider"></div>

            <a href="settings.php" class="nav-item active">
                <span class="nav-icon">⚙️</span>
                <span>Configurações</span>
            </a>
        </nav>

        <a href="logout.php" class="sidebar-logout">
            <span>🚪</span>
            <span>Sair</span>
        </a>
    </aside>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="main-content">

        <div class="page-header">
            <div>
                <h1 class="page-title">Configurações</h1>
                <p class="page-subtitle">Gerencie suas preferências e dados pessoais</p>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- PERFIL E SEGURANÇA -->
        <div class="card">
            <h2 class="card-title">Perfil & Segurança</h2>
            <form action="" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nome completo</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Nome de usuário</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>

                <div class="settings-divider">
                    <span>Alterar senha (opcional)</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Senha atual</label>
                        <input type="password" name="current_password" placeholder="Digite sua senha atual">
                    </div>
                    <div class="form-group">
                        <label>Nova senha</label>
                        <input type="password" name="new_password" placeholder="Digite a nova senha">
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirmar nova senha</label>
                    <input type="password" name="confirm_password" placeholder="Confirme a nova senha">
                </div>

                <div class="form-actions">
                    <button type="submit" name="update_profile" class="btn-primary btn-small">Salvar alterações</button>
                </div>
            </form>
        </div>

        <!-- SALÁRIO MENSAL -->
        <div class="card">
            <h2 class="card-title">Salário Mensal</h2>
            <p class="card-description">Defina seu salário mensal para acompanhar o quanto você já gastou.</p>
            <form action="" method="POST">
                <div class="salary-form">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <input type="text" name="salary" id="salaryInput" inputmode="numeric" placeholder="R$ 0,00" value="R$ <?php echo number_format($user['salary'], 2, ',', '.'); ?>">
                    </div>
                    <button type="submit" name="update_salary" class="btn-primary btn-small">Atualizar</button>
                </div>
            </form>
        </div>

        <!-- ORÇAMENTO POR CATEGORIA -->
        <div class="card" style="margin-bottom: 64px;">
        <h2 class="card-title">Orçamento por Categoria</h2>
        <p class="card-description">Defina um limite de gastos para cada categoria.</p>
        <?php if (empty($categories)): ?>
        <p class="empty-state">Nenhuma categoria cadastrada. <a href="categories.php" style="color: #7C3AED;">Criar categorias</a></p>
        <?php else: ?>
        <form action="" method="POST">
            <div class="budget-table-wrapper">
                
            <table class="table">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Orçamento</th>
                        <th>Total Gasto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td>
                                <span style="margin-right: 8px;"><?php echo $category['icon']; ?></span>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </td>
                            <td>
                                <input type="text" 
                                    name="budget[<?php echo $category['id']; ?>]"
                                    class="budget-input"
                                    inputmode="numeric"
                                    placeholder="R$ 0,00"
                                    value="<?php echo $category['budget'] > 0 ? 'R$ ' . number_format($category['budget'], 2, ',', '.') : ''; ?>">
                            </td>
                            <td>
                            <?php $over_budget = $category['budget'] > 0 && $category['total_spent'] > $category['budget'];?>
                                <span class="badge" style="<?php echo $over_budget ? 'background: rgba(220,38,38,0.15); color: #dc2626; border: 1px solid rgba(220,38,38,0.4);' : 'background: ' . $category['color'] . '25; color: ' . $category['color'] . '; border: 1px solid ' . $category['color'] . '50;'; ?>">
                                    <?php echo $over_budget ? '⚠️ ' : ''; ?>R$ <?php echo number_format($category['total_spent'], 2, ',', '.'); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            </div>
            <div class="form-actions" style="margin-top: 24px;">
                <button type="submit" name="update_budget" class="btn-primary btn-small">Salvar Orçamentos</button>
            </div>
        </form>
        <?php endif; ?>
        </div>
    </main>
</div>


    <script>
    const salaryInput = document.getElementById('salaryInput');

    // Formata o salário
    salaryInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        value = (parseInt(value) / 100).toFixed(2);
        this.value = 'R$ ' + value.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    });

    // Remove a mascara quando o campo recebe o foco
    salaryInput.addEventListener('focus', function() {
        if (this.value === 'R$ 0,00') {
            this.value = '';
        }
    });


    // Formata o valor inicial
    if (salaryInput.value && salaryInput.value !== 'R$ 0,00') {
        let value = salaryInput.value.replace(/\D/g, '');
        value = (parseInt(value) / 100).toFixed(2);
        salaryInput.value = 'R$ ' + value.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Bloqueia entrada de letras
      salaryInput.addEventListener('keydown', function(e) {
          const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
          if (!/[0-9]/.test(e.key) && !allowedKeys.includes(e.key)) {
              e.preventDefault();
          }
      });

    // Remove a máscara antes de enviar o formulário
    salaryInput.closest('form').addEventListener('submit', function() {
        let value = salaryInput.value.replace('R$ ', '').replace(/\./g, '').replace(',', '.');
        salaryInput.value = value;
    });

    // Máscara nos campos de orçamento
document.querySelectorAll('.budget-input').forEach(function(input) {
    input.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        value = (parseInt(value) / 100).toFixed(2);
        this.value = 'R$ ' + value.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    });

    input.addEventListener('keydown', function(e) {
        const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
        if (!/[0-9]/.test(e.key) && !allowedKeys.includes(e.key)) {
            e.preventDefault();
        }
    });
});

// Remove máscara antes de enviar
document.querySelector('form[action=""]').addEventListener('submit', function() {
    document.querySelectorAll('.budget-input').forEach(function(input) {
        let value = input.value.replace('R$ ', '').replace(/\./g, '').replace(',', '.');
        input.value = value;
    });
});
</script>
<script>
    function openSidebar() {
        document.querySelector('.sidebar').classList.add('open');
        document.getElementById('overlay').classList.add('active');
    }

    function closeSidebar() {
        document.querySelector('.sidebar').classList.remove('open');
        document.getElementById('overlay').classList.remove('active');
    }
</script>
</body>
</html>