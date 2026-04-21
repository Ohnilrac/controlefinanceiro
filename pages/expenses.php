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

$page_title  = 'Meus Gastos';
$active_page = 'expenses';
include __DIR__ . '/../includes/header.php';
?>

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

                <!-- PAGINAÇÃO -->
                <?php if ($total_pages > 1):
                    // Monta a base da URL preservando todos os filtros ativos.
                    // array_filter remove entradas vazias para não poluir a URL.
                    $pagination_base = '?' . http_build_query(array_filter([
                        'search'   => $search,
                        'category' => $filter_category,
                        'month'    => $filter_month,
                        'year'     => $filter_year,
                    ]));
                    // Se já há parâmetros, o separador é &; senão, é nada (append direto)
                    $sep = strpos($pagination_base, '=') !== false ? '&' : '';
                ?>
                    <div class="pagination">

                        <!-- Botão Anterior -->
                        <?php if ($current_page > 1): ?>
                            <a href="<?php echo $pagination_base . $sep; ?>page=<?php echo $current_page - 1; ?>" class="pagination-btn">← Anterior</a>
                        <?php else: ?>
                            <span class="pagination-btn pagination-disabled">← Anterior</span>
                        <?php endif; ?>

                        <!-- Números de página (exibe até 5, com reticências) -->
                        <?php
                        // Define a janela de páginas a exibir ao redor da atual
                        $window    = 2; // páginas antes e depois da atual
                        $page_from = max(1, $current_page - $window);
                        $page_to   = min($total_pages, $current_page + $window);

                        if ($page_from > 1): ?>
                            <a href="<?php echo $pagination_base . $sep; ?>page=1" class="pagination-btn">1</a>
                            <?php if ($page_from > 2): ?>
                                <span class="pagination-ellipsis">…</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($p = $page_from; $p <= $page_to; $p++): ?>
                            <?php if ($p === $current_page): ?>
                                <span class="pagination-btn pagination-active"><?php echo $p; ?></span>
                            <?php else: ?>
                                <a href="<?php echo $pagination_base . $sep; ?>page=<?php echo $p; ?>" class="pagination-btn"><?php echo $p; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page_to < $total_pages): ?>
                            <?php if ($page_to < $total_pages - 1): ?>
                                <span class="pagination-ellipsis">…</span>
                            <?php endif; ?>
                            <a href="<?php echo $pagination_base . $sep; ?>page=<?php echo $total_pages; ?>" class="pagination-btn"><?php echo $total_pages; ?></a>
                        <?php endif; ?>

                        <!-- Botão Próximo -->
                        <?php if ($current_page < $total_pages): ?>
                            <a href="<?php echo $pagination_base . $sep; ?>page=<?php echo $current_page + 1; ?>" class="pagination-btn">Próximo →</a>
                        <?php else: ?>
                            <span class="pagination-btn pagination-disabled">Próximo →</span>
                        <?php endif; ?>

                    </div>
                    <p class="pagination-info">
                        Exibindo <?php echo (($current_page - 1) * $per_page) + 1; ?>–<?php echo min($current_page * $per_page, $total_records); ?> de <?php echo $total_records; ?> gastos
                    </p>
                <?php endif; ?>

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
                        <input type="text" name="amount" id="amountInput" class="money-input" inputmode="numeric" placeholder="R$0,00" value="<?php echo $edit_expense ? 'R$ ' . number_format($edit_expense['amount'], 2, ',', '.') : 'R$ 0,00' ?>">
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

    <!-- Flag passada do PHP para o JS (indica se o modal abre em modo edicao) -->
    <script>
        const EXPENSE_EDIT_MODE = <?php echo $edit_expense ? 'true' : 'false'; ?>;
    </script>
    <script src="../assets/js/expenses.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
