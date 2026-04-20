<?php
/**
 * categories.logic.php
 *
 * Camada de lógica da página de categorias (CRUD).
 *
 * Responsabilidades:
 *  - Iniciar sessão e incluir PDO
 *  - Garantir usuário autenticado
 *  - Excluir categoria (validando que não há gastos vinculados)
 *  - Adicionar e editar categorias
 *  - Buscar todas as categorias do usuário com totais agregados
 *  - Carregar uma categoria para edição (via GET ?edit=ID)
 *
 * Variáveis exportadas para a view (categories.php):
 *  - $action_error, $action_success (string)
 *  - $categories (array): categorias do usuário com totais
 *  - $edit_category (array|null): categoria carregada para edição
 */

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id        = $_SESSION['user_id'];
$action_error   = '';
$action_success = '';

// EXCLUIR (via link ?delete=ID)
if (isset($_GET['delete'])) {
    $category_id = $_GET['delete'];

    // Verifica se a categoria possui gastos vinculados
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM expenses WHERE category_id = :category_id AND user_id = :user_id');
    $stmt->execute([':category_id' => $category_id, ':user_id' => $user_id]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    if ($count > 0) {
        $action_error = 'Não é possível excluir uma categoria com gastos vinculados.';
    } else {
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':id' => $category_id, ':user_id' => $user_id]);
        header('Location: categories.php');
        exit();
    }
}

// ADICIONAR ou EDITAR (POST do modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $color = trim($_POST['color'] ?? '#7C3AED');
    $icon  = trim($_POST['icon'] ?? '💰');

    if (empty($name)) {
        $action_error = 'Por favor, informe o nome da categoria.';
    } else {
        if (!empty($_POST['category_id'])) {
            // EDIÇÃO
            $stmt = $pdo->prepare('UPDATE categories SET name = :name, color = :color, icon = :icon WHERE id = :id AND user_id = :user_id');
            $stmt->execute([
                ':name'    => $name,
                ':color'   => $color,
                ':icon'    => $icon,
                ':id'      => $_POST['category_id'],
                ':user_id' => $user_id,
            ]);
            $action_success = 'Categoria atualizada com sucesso!';
        } else {
            // INSERÇÃO
            $stmt = $pdo->prepare('INSERT INTO categories (user_id, name, color, icon) VALUES (:user_id, :name, :color, :icon)');
            $stmt->execute([
                ':user_id' => $user_id,
                ':name'    => $name,
                ':color'   => $color,
                ':icon'    => $icon,
            ]);
            $action_success = 'Categoria adicionada com sucesso!';
        }
        header('Location: categories.php');
        exit();
    }
}

// Lista todas as categorias do usuário com totais agregados.
// LEFT JOIN garante que categorias sem gastos também apareçam.
// COALESCE substitui NULL (sem gastos) por 0.
$stmt = $pdo->prepare('
    SELECT c.*, COUNT(e.id) as total_expenses, COALESCE(SUM(e.amount), 0) as total_spent
    FROM categories c
    LEFT JOIN expenses e ON c.id = e.category_id
    WHERE c.user_id = :user_id
    GROUP BY c.id
    ORDER BY c.name
');
$stmt->execute([':user_id' => $user_id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Carrega categoria para edição (modal abre preenchido)
$edit_category = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = :id AND user_id = :user_id');
    $stmt->execute([':id' => $_GET['edit'], ':user_id' => $user_id]);
    $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
}
