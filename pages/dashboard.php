<?php
/**
 * dashboard.php (view)
 *
 * Camada de apresentação do dashboard.
 * Toda a lógica está em dashboard.logic.php.
 *
 * Variáveis disponíveis na view:
 *  - $user, $total_spent, $remaining, $percentage,
 *    $expenses_by_category, $recent_transactions, $monthly_evolution
 */
require_once __DIR__ . '/dashboard.logic.php';

$page_title  = 'Dashboard';
$active_page = 'dashboard';
include __DIR__ . '/../includes/header.php';
?>

            <!-- HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Dashboard</h1>
                    <p class="page-subtitle">Bem-vindo, <?php echo htmlspecialchars($user['full_name']); ?>!</p>
                </div>
            </div>

            <!-- CARDS DE RESUMO -->
            <div class="summary-cards">
                <div class="card summary-card">
                    <div class="card-icon" style="background: rgba(124, 58, 237, 0.2);">💰</div>
                    <div class="card-info">
                        <p class="card-label">Salário Mensal</p>
                        <p class="card-value">R$ <?php echo number_format($user['salary'], 2, ',', '.'); ?></p>
                    </div>
                </div>

                <div class="card summary-card">
                    <div class="card-icon" style="background: rgba(220, 38, 38, 0.2);">💸</div>
                    <div class="card-info">
                        <p class="card-label">Total Gasto</p>
                        <p class="card-value">R$ <?php echo number_format($total_spent, 2, ',', '.'); ?></p>
                        <?php if ($spent_diff !== null): ?>
                            <p class="card-comparison <?php echo $spent_diff > 0 ? 'comparison-up' : 'comparison-down'; ?>">
                                <?php echo $spent_diff > 0 ? '▲' : '▼'; ?>
                                R$ <?php echo number_format(abs($spent_diff), 2, ',', '.'); ?>
                                vs mês anterior
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card summary-card">
                    <div class="card-icon" style="background: rgba(16, 185, 129, 0.2);">💵</div>
                    <div class="card-info">
                        <p class="card-label">Saldo Restante</p>
                        <p class="card-value <?php echo $remaining < 0 ? 'text-danger' : 'text-success'; ?>">
                            R$ <?php echo number_format($remaining, 2, ',', '.'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- BARRA DE PROGRESSO -->
            <div class="card progress-card">
                <div class="progress-header">
                    <span>Uso do Salário</span>
                    <span><?php echo $percentage; ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo min($percentage, 100); ?>%; background: <?php echo $percentage > 80 ? '#DC2626' : '#7C3AED'; ?>;"></div>
                </div>
                <p class="progress-info">R$ <?php echo number_format($total_spent, 2, ',', '.'); ?> de R$ <?php echo number_format($user['salary'], 2, ',', '.'); ?> utilizados</p>
            </div>

            <!-- ALERTAS DE ORÇAMENTO ESTOURADO -->
            <?php if (!empty($over_budget_categories)): ?>
                <div class="card budget-alert-card">
                    <h2 class="card-title">⚠️ Orçamento Estourado</h2>
                    <p class="card-description">As categorias abaixo ultrapassaram o limite definido este mês.</p>
                    <div class="budget-alert-list">
                        <?php foreach ($over_budget_categories as $cat): ?>
                            <div class="budget-alert-item">
                                <div class="budget-alert-info">
                                    <span class="budget-alert-icon"><?php echo $cat['icon']; ?></span>
                                    <span class="budget-alert-name"><?php echo htmlspecialchars($cat['name']); ?></span>
                                </div>
                                <div class="budget-alert-values">
                                    <span class="budget-alert-spent">R$ <?php echo number_format($cat['total'], 2, ',', '.'); ?></span>
                                    <span class="budget-alert-limit">limite: R$ <?php echo number_format($cat['budget'], 2, ',', '.'); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- GRÁFICO E CATEGORIAS -->
            <div class="grid-2">

                <!-- GRÁFICO -->
                <div class="card">
                    <h2 class="card-title">Evolução de Gastos</h2>
                    <canvas id="expenseChart"></canvas>
                </div>

                <!-- GASTOS POR CATEGORIA -->
                <div class="card">
                    <h2 class="card-title">Gastos por Categoria</h2>
                    <?php if (empty($expenses_by_category)): ?>
                        <p class="empty-state">Nenhum gasto registrado este mês.</p>
                    <?php else: ?>
                        <?php foreach ($expenses_by_category as $category): ?>
                            <div class="category-item">
                                <div class="category-info">
                                    <span><?php echo $category['icon']; ?></span>
                                    <span><?php echo htmlspecialchars($category['name']); ?></span>
                                </div>
                                <span class="category-value">R$ <?php echo number_format($category['total'], 2, ',', '.'); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>

            <!-- TRANSAÇÕES RECENTES -->
            <div class="card last-card">
                <div class="card-header">
                    <h2 class="card-title">Transações Recentes</h2>
                    <a href="expenses.php" class="view-all">Ver todas</a>
                </div>
                <?php if (empty($recent_transactions)): ?>
                    <p class="empty-state">Nenhuma transação registrada.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Categoria</th>
                                <th>Data</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_transactions as $transaction): ?>
                                <tr>
                                    <td data-label="Nome"><?php echo htmlspecialchars($transaction['name']); ?></td>
                                    <td data-label="Categoria">
                                        <span class="badge" style="background: <?php echo $transaction['color']; ?>30; color: <?php echo $transaction['color']; ?>;">
                                            <?php echo $transaction['icon']; ?> <?php echo htmlspecialchars($transaction['category_name']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Data"><?php echo date('d/m/Y', strtotime($transaction['date'])); ?></td>
                                    <td class="text-danger" data-label="Valor">- R$ <?php echo number_format($transaction['amount'], 2, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        </main>
    </div>
    <!-- Dependencia do grafico (Chart.js via CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Dados do grafico vindos do PHP (consumidos pelo dashboard.js) -->
    <script>
        const DASHBOARD_DATA = {
            monthlyEvolution: <?php echo json_encode($monthly_evolution); ?>
        };
    </script>
    <script src="../assets/js/dashboard.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
