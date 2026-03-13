<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$action_error = '';
$action_success = '';

// DELETAR
if (isset($_GET['delete'])) {
    $category_id = $_GET['delete'];
    
    // Verifica se tem gastos vinculados
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

// ADICIONAR / EDITAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $color = trim($_POST['color'] ?? '#7C3AED');
    $icon = trim($_POST['icon'] ?? '💰');

    if (empty($name)) {
        $action_error = 'Por favor, informe o nome da categoria.';
    } else {
        if (!empty($_POST['category_id'])) {
            // Editar categoria
            $stmt = $pdo->prepare('UPDATE categories SET name = :name, color = :color, icon = :icon WHERE id = :id AND user_id = :user_id');
            $stmt->execute([
                ':name' => $name,
                ':color' => $color,
                ':icon' => $icon,
                ':id' => $_POST['category_id'],
                ':user_id' => $user_id
            ]);
            $action_success = 'Categoria atualizada com sucesso!';
        } else {
            // Adicionar categoria
            $stmt = $pdo->prepare('INSERT INTO categories (user_id, name, color, icon) VALUES (:user_id, :name, :color, :icon)');
            $stmt->execute([
                ':user_id' => $user_id,
                ':name' => $name,
                ':color' => $color,
                ':icon' => $icon
            ]);
            $action_success = 'Categoria adicionada com sucesso!';
        }
        header('Location: categories.php');
        exit();
    }
}

// Busca todas as categorias do usuário
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

// Busca categoria para edição
$edit_category = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = :id AND user_id = :user_id');
    $stmt->execute([':id' => $_GET['edit'], ':user_id' => $user_id]);
    $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias - Controle Financeiro</title>
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
            <a href="categories.php" class="nav-item active">
                <span class="nav-icon">🏷️</span>
                <span>Categorias</span>
            </a>

            <div class="nav-divider"></div>

            <a href="settings.php" class="nav-item">
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
                <h1 class="page-title">Categorias</h1>
                <p class="page-subtitle">Gerencie suas categorias de gastos</p>
            </div>
            <button class="btn-primary btn-small" onclick="openModal()">+ Adicionar Categoria</button>
        </div>

        <?php if (!empty($action_error)): ?>
            <div class="alert alert-error"><?php echo $action_error; ?></div>
        <?php endif; ?>

        <?php if (!empty($action_success)): ?>
            <div class="alert alert-success"><?php echo $action_success; ?></div>
        <?php endif; ?>

        <!-- GRID DE CATEGORIAS -->
        <?php if (empty($categories)): ?>
            <div class="card">
                <p class="empty-state">Nenhuma categoria cadastrada. Crie sua primeira categoria!</p>
            </div>
        <?php else: ?>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <div class="category-card" style="border-top: 4px solid <?php echo $category['color']; ?>;">
                        <div class="category-card-header">
                            <div class="category-card-icon" style="background: <?php echo $category['color']; ?>20;">
                                <?php echo $category['icon']; ?>
                            </div>
                            <div class="category-card-actions">
                                <a href="?edit=<?php echo $category['id']; ?>" class="btn-icon btn-edit">✏️</a>
                                <a href="?delete=<?php echo $category['id']; ?>" class="btn-icon btn-delete" onclick="return confirm('Tem certeza que deseja excluir esta categoria?')">🗑️</a>
                            </div>
                        </div>
                        <h3 class="category-card-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p class="category-card-total">R$ <?php echo number_format($category['total_spent'], 2, ',', '.'); ?></p>
                        <p class="category-card-count"><?php echo $category['total_expenses']; ?> gasto(s)</p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>
</div>
    <!-- MODAL -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
        <div class="modal">
            <div class="modal-header">
                <h2><?php echo $edit_category ? 'Editar Categoria' : 'Adicionar Categoria'; ?></h2>
                <button class="modal-close" onclick="closeModal()">✕</button>
            </div>

            <form action="" method="POST">
                <?php if ($edit_category): ?>
                    <input type="hidden" name="category_id" value="<?php echo $edit_category['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Nome da categoria</label>
                    <input type="text" name="name" placeholder="Ex: Alimentação, Transporte..." value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
    <label>Ícone</label>
    <input type="text" name="icon" id="iconInput" placeholder="Selecione um ícone abaixo" value="<?php echo $edit_category ? $edit_category['icon'] : ''; ?>" readonly>
    <div class="emoji-grid">
        <span class="emoji-option" onclick="selectEmoji('💰')">💰</span>
        <span class="emoji-option" onclick="selectEmoji('🍔')">🍔</span>
        <span class="emoji-option" onclick="selectEmoji('🚗')">🚗</span>
        <span class="emoji-option" onclick="selectEmoji('🏠')">🏠</span>
        <span class="emoji-option" onclick="selectEmoji('🎮')">🎮</span>
        <span class="emoji-option" onclick="selectEmoji('👕')">👕</span>
        <span class="emoji-option" onclick="selectEmoji('💊')">💊</span>
        <span class="emoji-option" onclick="selectEmoji('📚')">📚</span>
        <span class="emoji-option" onclick="selectEmoji('✈️')">✈️</span>
        <span class="emoji-option" onclick="selectEmoji('🐾')">🐾</span>
        <span class="emoji-option" onclick="selectEmoji('💡')">💡</span>
        <span class="emoji-option" onclick="selectEmoji('📱')">📱</span>
        <span class="emoji-option" onclick="selectEmoji('🎵')">🎵</span>
        <span class="emoji-option" onclick="selectEmoji('🏋️')">🏋️</span>
        <span class="emoji-option" onclick="selectEmoji('🍕')">🍕</span>
        <span class="emoji-option" onclick="selectEmoji('☕')">☕</span>
        <span class="emoji-option" onclick="selectEmoji('🛒')">🛒</span>
        <span class="emoji-option" onclick="selectEmoji('💻')">💻</span>
        <span class="emoji-option" onclick="selectEmoji('🎓')">🎓</span>
        <span class="emoji-option" onclick="selectEmoji('🏥')">🏥</span>
        <span class="emoji-option" onclick="selectEmoji('⚡')">⚡</span>
        <span class="emoji-option" onclick="selectEmoji('💧')">💧</span>
        <span class="emoji-option" onclick="selectEmoji('🐶')">🐶</span>
        <span class="emoji-option" onclick="selectEmoji('🎁')">🎁</span>
    </div>
</div>

<div class="form-group">
    <label>Cor</label>
    <input type="color" name="color" value="<?php echo $edit_category ? $edit_category['color'] : '#7C3AED'; ?>" class="input-color">
</div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modalOverlay = document.getElementById('modalOverlay');

        function openModal() {
            modalOverlay.classList.add('active');
        }

        function closeModal(event) {
            if (!event || event.target === modalOverlay) {
                modalOverlay.classList.remove('active');
            }
        }

        <?php if ($edit_category): ?>
            openModal();
        <?php endif; ?>

        function selectEmoji(emoji) {
    document.getElementById('iconInput').value = emoji;
    document.querySelectorAll('.emoji-option').forEach(el => el.classList.remove('selected'));
    event.target.classList.add('selected');
}
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