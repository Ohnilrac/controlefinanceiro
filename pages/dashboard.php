<?php 
ini_set('display_errors', 1);

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute([':id' => $user_id]);
//PDO::FETCH_ASSOC é um modo de busca que retorna os resultados como um array associativo, onde as chaves do array correspondem aos nomes das colunas do banco de dados. Isso permite acessar os valores usando os nomes das colunas em vez de índices numéricos.
$user = $stmt->fetch(PDO::FETCH_ASSOC);

//Mes e atual e ano atual para calcular o total gasto no mês
$month = date('m');
$year = date('Y');

//Consulta para calcular o total gasto no mês atual
$stmt = $pdo->prepare('SELECT SUM(amount) AS total FROM expenses WHERE user_id = :user_id AND MONTH(date) = :month AND YEAR(date) = :year');
$stmt->execute([':user_id' => $user_id, ':month' => $month, ':year' => $year]);
$total_spent = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

//Calcula o saldo restante
$remaining = $user['salary'] - $total_spent;

//porcentagem do salário gasto
$percentage = $user['salary'] > 0 ? ($total_spent / $user['salary']) * 100 : 0;

// Gasto por categoria no mes
$stmt = $pdo->prepare('SELECT c.name, c.color, c.icon, SUM(e.amount) AS total FROM expenses e JOIN categories c ON e.category_id = c.id WHERE e.user_id = :user_id AND MONTH(e.date) = :month AND YEAR(e.date) = :year GROUP BY c.id, c.name, c.color, c.icon ORDER BY total DESC');
$stmt->execute([':user_id' => $user_id, ':month' => $month, ':year' => $year]);
$expenses_by_category = $stmt->fetchAll(PDO::FETCH_ASSOC);

//ultimas 5 despesas
$stmt = $pdo->prepare('SELECT e.*, c.name as category_name, c.color, c.icon FROM expenses e JOIN categories c ON e.category_id = c.id WHERE e.user_id = :user_id ORDER BY e.date DESC, e.created_at DESC LIMIT 5');
$stmt->execute([':user_id' => $user_id]);
$recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

//evolução ultimos 6 meses
$stmt = $pdo->prepare('SELECT MONTH(date) AS month, YEAR(date) AS year, SUM(amount) AS total FROM expenses WHERE user_id = :user_id AND date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY YEAR(date), MONTH(date) ORDER BY YEAR(date), MONTH(date)'); 
$stmt->execute([':user_id' => $user_id]);
$monthly_evolution = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Controle Financeiro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">

<!-- OVERLAY -->
<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<!-- TOPBAR MOBILE -->
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
                <a href="dashboard.php" class="nav-item active">
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('expenseChart').getContext('2d');
        const monthlyData = <?php echo json_encode($monthly_evolution); ?>;

        const labels = monthlyData.map(item => {
            const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            return months[item.month - 1];
        });

        const data = monthlyData.map(item => item.total);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Gastos',
                    data: data,
                    backgroundColor: '#7C3AED',
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { color: '#2E2E4E' }, ticks: { color: '#9090A0' } },
                    y: { grid: { color: '#2E2E4E' }, ticks: { color: '#9090A0' } }
                }
            }
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