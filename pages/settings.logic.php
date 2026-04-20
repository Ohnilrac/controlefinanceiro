<?php
/**
 * settings.logic.php
 *
 * Camada de lógica da página de configurações.
 *
 * A página possui 3 ações distintas, controladas por nomes diferentes
 * no botão de submit (update_profile, update_salary, update_budget):
 *  1. Atualizar perfil + senha (opcional)
 *  2. Atualizar salário mensal
 *  3. Atualizar orçamentos por categoria
 *
 * Responsabilidades:
 *  - Iniciar sessão e incluir PDO
 *  - Garantir usuário autenticado
 *  - Carregar dados do usuário e categorias com totais gastos
 *  - Processar cada um dos três formulários acima
 *  - Recarregar dados após cada update para a view exibir o estado atualizado
 *
 * Variáveis exportadas para a view (settings.php):
 *  - $user (array)
 *  - $categories (array)
 *  - $error, $success (string)
 */

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error   = '';
$success = '';

// Carrega dados do usuário
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Carrega categorias com total gasto (para a tabela de orçamentos)
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

    // ATUALIZAR PERFIL E (opcionalmente) SENHA
    if (isset($_POST['update_profile'])) {
        $full_name        = trim($_POST['full_name'] ?? '');
        $username         = trim($_POST['username'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $current_password = trim($_POST['current_password'] ?? '');
        $new_password     = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        if (empty($full_name) || empty($username) || empty($email)) {
            $error = 'Por favor, preencha todos os campos obrigatórios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Por favor, insira um email válido.';
        } else {
            // Verifica se username/email já estão em uso por OUTRO usuário
            // (id != :id garante que o próprio usuário não dispare o erro)
            $stmt = $pdo->prepare('SELECT id FROM users WHERE (email = :email OR username = :username) AND id != :id');
            $stmt->execute([':email' => $email, ':username' => $username, ':id' => $user_id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $error = 'Email ou nome de usuário já está em uso por outro usuário.';
            } else {
                // Atualiza dados de perfil
                $stmt = $pdo->prepare('UPDATE users SET full_name = :full_name, username = :username, email = :email WHERE id = :id');
                $stmt->execute([':full_name' => $full_name, ':username' => $username, ':email' => $email, ':id' => $user_id]);

                // Atualiza senha apenas se ambos os campos foram preenchidos
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

                // Atualiza nome na sessão (para refletir em todas as páginas)
                $_SESSION['user_name'] = $full_name;

                // Recarrega dados do usuário para a view exibir os valores novos
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

        // Recarrega dados do usuário para refletir o novo salário
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ATUALIZAR ORÇAMENTO POR CATEGORIA
    if (isset($_POST['update_budget'])) {
        // O input vem com máscara "R$ 1.234,56" — precisamos limpar para float
        $budgets = $_POST['budget'] ?? [];
        foreach ($budgets as $category_id => $budget_value) {
            $budget_value = str_replace(['R$ ', '.'], '', $budget_value);
            $budget_value = str_replace(',', '.', $budget_value);
            $budget_value = floatval($budget_value);
            $stmt = $pdo->prepare('UPDATE categories SET budget = :budget WHERE id = :id AND user_id = :user_id');
            $stmt->execute([':budget' => $budget_value, ':id' => $category_id, ':user_id' => $user_id]);
        }
        $success = 'Orçamentos atualizados com sucesso!';

        // Recarrega categorias para a view exibir os novos orçamentos
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
