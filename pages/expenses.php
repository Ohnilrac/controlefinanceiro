<?php 
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$account_year = date('Y', strtotime($user['created_at']));
$min_year = min($account_year, date('Y') - 2);

//Filtros
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';
$filter_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$filter_year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

//Buscar categora para filtros
$stmt = $pdo->prepare('SELECT * FROM categories WHERE user_id = :user_id ORDER BY name');
$stmt->execute([':user_id' => $user_id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Query para buscar despesas com filtros
$sql = 'SELECT e.*, c.name AS category_name, c.color, c.icon FROM expenses e LEFT JOIN categories c ON e.category_id = c.id WHERE e.user_id = :user_id';
$params = [':user_id' => $user_id];

if (!empty($filter_category)) {
    $sql .= ' AND e.category_id = :category_id';
    $params[':category_id'] = $filter_category;
}
if (!empty($filter_month) && !empty($filter_year)) {
    $sql .= ' AND MONTH(e.date) = :month AND YEAR(e.date) = :year';
    $params[':month'] = $filter_month;
    $params[':year'] = $filter_year;
}

if (!empty($search)) {
    $sql .= ' AND e.name LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}

$sql .= ' ORDER BY e.date DESC, e.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

//ações de adicionar, editar e excluir despesas
$action_error = '';
$action_success = '';

//deletar despesa
if (isset($_GET['delete'])) {
  $expense_id = $_GET['delete'];
  $stmt = $pdo->prepare('DELETE FROM expenses WHERE id = :id AND user_id = :user_id');
  $stmt->execute([':id' => $expense_id, ':user_id' => $user_id]);
  header('Location: expenses.php');
  exit();
}

// adicionar ou editar despesa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $category_id = trim($_POST['category_id'] ?? '');
  $amount = trim($_POST['amount'] ?? '');
  $date = trim($_POST['date'] ?? '');
  $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
  $comment = trim($_POST['comments'] ?? '');

  if (empty($name) || empty($amount) || empty($category_id) || empty($date)) {
    $action_error = 'Por favor, preencha todos os campos obrigatórios.';
  } else {
      if (!empty($_POST['expense_id'])) {
      // Editar despesa
        $stmt = $pdo->prepare('UPDATE expenses SET name = :name, category_id = :category_id, amount = :amount, date = :date, is_recurring = :is_recurring, comment = :comment WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
        ':name' => $name,
        ':category_id' => $category_id,
        ':amount' => $amount,
        ':date' => $date,
        ':is_recurring' => $is_recurring,
        ':comment' => $comment,
        ':id' => $_POST['expense_id'],
        ':user_id' => $user_id
      ]);
    $action_success = 'Despesa atualizada com sucesso.';
  } else {
    // Adicionar nova despesa
    $stmt = $pdo->prepare('INSERT INTO expenses (user_id, name, category_id, amount, date, is_recurring, comment) VALUES (:user_id, :name, :category_id, :amount, :date, :is_recurring, :comment)');
    $stmt->execute([
      ':user_id' => $user_id,
      ':name' => $name,
      ':category_id' => $category_id,
      ':amount' => $amount,
      ':date' => $date,
      ':is_recurring' => $is_recurring,
      ':comment' => $comment
    ]);
    $action_success = 'Despesa adicionada com sucesso.';
  }
  header('Location: expenses.php');
  exit();
}
}

// Buscar despesa para edição
$edit_expense = null;
if (isset($_GET['edit'])) {
  $stmt = $pdo->prepare('SELECT * FROM expenses WHERE id = :id AND user_id = :user_id');
  $stmt->execute([':id' => $_GET['edit'], ':user_id' => $user_id]);
  $edit_expense = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Gastos - Controle Financeiro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">

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
            <a href="expenses.php" class="nav-item active">
                <span class="nav-icon">📋</span>
                <span>Meus Gastos</span>
            </a>
            <a href="categories.php" class="nav-item">
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
                <h1 class="page-title">Meus Gastos</h1>
                <p class="page-subtitle">Gerencie e acompanhe seus gastos</p>
            </div>
            <button class="btn-primary btn-small" onclick="openModal()">+ Adicionar Gasto</button>
        </div>

        <?php if (!empty($action_error)): ?>
            <div class="alert alert-error"><?php echo $action_error; ?></div>
        <?php endif; ?>

        <?php if (!empty($action_success)): ?>
            <div class="alert alert-success"><?php echo $action_success; ?></div>
        <?php endif; ?>

        <!-- FILTROS -->
        <div class="card">
            <form action="" method="GET" class="filters">
                <input type="text" name="search" placeholder="Buscar gasto..." value="<?php echo htmlspecialchars($search); ?>" class="filter-input">

                <select name="category" class="filter-select">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $filter_category == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="month" class="filter-select">
                    <?php
                    $months = ['01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril', '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'];
                    foreach ($months as $num => $name): ?>
                        <option value="<?php echo $num; ?>" <?php echo $filter_month == $num ? 'selected' : ''; ?>>
                            <?php echo $name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="year" class="filter-select">
                    <?php for ($y = date('Y'); $y >= $min_year; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo $filter_year == $y ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <button type="submit" class="btn-secondary">Filtrar</button>
                <a href="expenses.php" class="btn-secondary">Limpar</a>
            </form>
        </div>

        <!-- TABELA -->
        <div class="card">
            <?php if (empty($expenses)): ?>
                <p class="empty-state">Nenhum gasto encontrado.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Data</th>
                            <th>Recorrente</th>
                            <th>Valor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($expense['name']); ?>
                                    <?php if (!empty($expense['comment'])): ?>
                                        <span class="comment-icon" title="<?php echo htmlspecialchars($expense['comment']); ?>">💬</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge" style="background: <?php echo $expense['color']; ?>30; color: <?php echo $expense['color']; ?>;">
                                        <?php echo $expense['icon']; ?> <?php echo htmlspecialchars($expense['category_name']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($expense['date'])); ?></td>
                                <td><?php echo $expense['is_recurring'] ? '🔄 Sim' : 'Não'; ?></td>
                                <td class="text-danger">- R$ <?php echo number_format($expense['amount'], 2, ',', '.'); ?></td>
                                <td class="actions">
                                    <a href="?edit=<?php echo $expense['id']; ?>" class="btn-icon btn-edit">✏️</a>
                                    <a href="?delete=<?php echo $expense['id']; ?>" class="btn-icon btn-delete" onclick="return confirm('Tem certeza que deseja excluir este gasto?')">🗑️</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </main>

    <!-- MODAL -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
        <div class="modal">
            <div class="modal-header">
                <h2><?php echo $edit_expense ? 'Editar Gasto' : 'Adicionar Gasto'; ?></h2>
                <button class="modal-close" onclick="closeModal()">✕</button>
            </div>

            <form action="" method="POST">
                <?php if ($edit_expense): ?>
                    <input type="hidden" name="expense_id" value="<?php echo $edit_expense['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="name" placeholder="Ex: Almoço, Conta de luz..." value="<?php echo $edit_expense ? htmlspecialchars($edit_expense['name']) : ''; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Categoria</label>
                        <select name="category_id" class="filter-select" style="width:100%">
                            <option value="">Selecione...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($edit_expense && $edit_expense['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo $cat['icon']; ?> <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Valor (R$)</label>
                        <input type="number" name="amount" step="0.01" min="0" placeholder="0,00" value="<?php echo $edit_expense ? $edit_expense['amount'] : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Data</label>
                    <input type="date" name="date" min="<?php echo $min_year; ?>-01-01" max="<?php echo date('Y'); ?>-12-31" value="<?php echo $edit_expense ? $edit_expense['date'] : date('Y-m-d'); ?>">
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" name="is_recurring" id="is_recurring" <?php echo ($edit_expense && $edit_expense['is_recurring']) ? 'checked' : ''; ?>>
                    <label for="is_recurring">Gasto recorrente</label>
                </div>

                <div class="form-group">
                    <label>Comentário (opcional)</label>
                    <textarea name="comment" placeholder="Adicione um comentário..."><?php echo $edit_expense ? htmlspecialchars($edit_expense['comment']) : ''; ?></textarea>
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

        // Abre o modal automaticamente se estiver em modo de edição
        <?php if ($edit_expense): ?>
            openModal();
        <?php endif; ?>
    </script>

</body>
</html>