<?php
/**
 * expenses.php (view)
 *
 * Camada de apresentação da página de gastos.
 * Toda a lógica está em expenses.logic.php.
 *
 * Variáveis disponíveis na view:
 *  - $user, $min_year, $filter_category, $filter_month, $filter_year, $search
 *  - $categories, $expenses, $action_error, $action_success, $edit_expense
 */
require_once __DIR__ . '/expenses.logic.php';
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
            <div class="filters-header">
                <span>🔍 Filtrar gastos</span>
            </div>
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

                <button type="submit" class="btn-filter">Filtrar</button>
                <a href="expenses.php" class="btn-filter">Limpar</a>
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
                                <td data-label="Nome">
                                <div class="expense-name">
                                    <?php echo htmlspecialchars($expense['name']); ?>
                                    <?php if (!empty($expense['comment'])): ?>
                                        <span class="expense-comment"><?php echo htmlspecialchars($expense['comment']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                                <td data-label="Categoria">
                                    <span class="badge" style="background: <?php echo $expense['color']; ?>30; color: <?php echo $expense['color']; ?>;">
                                        <?php echo $expense['icon']; ?> <?php echo htmlspecialchars($expense['category_name']); ?>
                                    </span>
                                </td>
                                <td data-label="Data"><?php echo date('d/m/Y', strtotime($expense['date'])); ?></td>
                                <td data-label="Recorrente"><?php echo $expense['is_recurring'] ? '🔄 Sim' : 'Não'; ?></td>
                                <td class="text-danger" data-label="Valor">- R$ <?php echo number_format($expense['amount'], 2, ',', '.'); ?></td>
                                <td class="actions">
                                    <a href="?edit=<?php echo $expense['id']; ?>" class="btn-icon btn-edit">✏️ Editar</a>
                                    <a href="?delete=<?php echo $expense['id']; ?>" class="btn-icon btn-delete" onclick="return confirm('Tem certeza que deseja excluir este gasto?')">🗑️ Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </main>
</div>
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
                        <input type="text" name="amount" id="amountInput" inputmode="numeric" placeholder="R$0,00" value="<?php echo $edit_expense ? 'R$ ' . number_format($edit_expense['amount'], 2, ',', '.') : 'R$ 0,00' ?>">
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

                // limpar o parametro de url se a pessoa saiu do modal de edição sem fazer nada
                const url = new URL(window.location);
                url.searchParams.delete('edit');
                window.history.replaceState({}, '', url);

                // limpar campos do modal
                document.querySelector('.modal form').reset();
                document.querySelector('input[name="expense_id"]') && document.querySelector('input[name="expense_id"]').remove();
                document.querySelector('.modal h2').textContent = 'Adicionar Gasto';
                amountInput.value = '';
            }
        }

        // Abre o modal automaticamente se estiver em modo de edição
        <?php if ($edit_expense): ?>
            openModal();
        <?php endif; ?>

        const amountInput = document.getElementById('amountInput');

amountInput.addEventListener('input', function() {
    let value = this.value.replace(/\D/g, '');
    value = (parseInt(value) / 100).toFixed(2);
    this.value = 'R$ ' + value.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
});

amountInput.addEventListener('keydown', function(e) {
    const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
    if (!/[0-9]/.test(e.key) && !allowedKeys.includes(e.key)) {
        e.preventDefault();
    }
});

amountInput.closest('form').addEventListener('submit', function() {
    let value = amountInput.value.replace('R$ ', '').replace(/\./g, '').replace(',', '.');
    amountInput.value = value;
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
