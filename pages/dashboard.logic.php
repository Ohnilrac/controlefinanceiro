<?php
/**
 * dashboard.logic.php
 *
 * Camada de lógica da página principal (dashboard).
 *
 * Responsabilidades:
 *  - Iniciar sessão
 *  - Incluir conexão PDO
 *  - Garantir que o usuário esteja autenticado (senão, redireciona para login)
 *  - Calcular total gasto no mês, saldo restante e percentual do salário
 *  - Buscar gastos por categoria do mês
 *  - Buscar últimas 5 transações
 *  - Buscar evolução dos últimos 6 meses para o gráfico
 *
 * Variáveis exportadas para a view (dashboard.php):
 *  - $user (array): dados do usuário autenticado
 *  - $total_spent (float): total gasto no mês atual
 *  - $remaining (float): saldo restante (salário - gasto)
 *  - $percentage (float): percentual do salário consumido
 *  - $expenses_by_category (array): gastos agrupados por categoria
 *  - $recent_transactions (array): últimas 5 despesas
 *  - $monthly_evolution (array): dados para o gráfico Chart.js
 */

ini_set('display_errors', 1);

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

// Bloqueia acesso de quem não está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Busca dados completos do usuário (nome, salário etc.)
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute([':id' => $user_id]);
// PDO::FETCH_ASSOC retorna o resultado como array associativo,
// permitindo acessar valores pelo nome da coluna em vez de índice numérico.
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Mês e ano atuais para os filtros
$month = date('m');
$year  = date('Y');

// Total gasto no mês atual
$stmt = $pdo->prepare('SELECT SUM(amount) AS total FROM expenses WHERE user_id = :user_id AND MONTH(date) = :month AND YEAR(date) = :year');
$stmt->execute([':user_id' => $user_id, ':month' => $month, ':year' => $year]);
// O ?? 0 protege contra NULL caso não haja gastos no mês
$total_spent = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Total gasto no mês anterior (para o comparativo no card "Total Gasto")
// DATE_FORMAT + DATE_SUB calcula o mês passado sem precisar tratar virada de ano
$stmt = $pdo->prepare('SELECT SUM(amount) AS total FROM expenses WHERE user_id = :user_id AND MONTH(date) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND YEAR(date) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))');
$stmt->execute([':user_id' => $user_id]);
$last_month_spent = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? null;

// Diferença em relação ao mês anterior (null = sem dados para comparar)
// $spent_diff > 0 = gastou mais; < 0 = gastou menos
$spent_diff = ($last_month_spent !== null) ? $total_spent - $last_month_spent : null;

// Saldo restante e percentual do salário consumido
$remaining  = $user['salary'] - $total_spent;
$percentage = $user['salary'] > 0 ? ($total_spent / $user['salary']) * 100 : 0;

// Gastos por categoria no mês atual (com nome, cor, ícone e orçamento definido)
// O campo budget é incluído para que a view possa exibir alertas de orçamento estourado
$stmt = $pdo->prepare('SELECT c.name, c.color, c.icon, c.budget, SUM(e.amount) AS total FROM expenses e JOIN categories c ON e.category_id = c.id WHERE e.user_id = :user_id AND MONTH(e.date) = :month AND YEAR(e.date) = :year GROUP BY c.id, c.name, c.color, c.icon, c.budget ORDER BY total DESC');
$stmt->execute([':user_id' => $user_id, ':month' => $month, ':year' => $year]);
$expenses_by_category = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filtra as categorias que têm orçamento definido (budget > 0) e que o total gasto
// no mês ultrapassa esse orçamento. Esse array alimenta o bloco de alertas no dashboard.
$over_budget_categories = array_filter($expenses_by_category, function($cat) {
    return $cat['budget'] > 0 && $cat['total'] > $cat['budget'];
});

// Últimas 5 despesas (com dados da categoria via JOIN)
$stmt = $pdo->prepare('SELECT e.*, c.name as category_name, c.color, c.icon FROM expenses e JOIN categories c ON e.category_id = c.id WHERE e.user_id = :user_id ORDER BY e.date DESC, e.created_at DESC LIMIT 5');
$stmt->execute([':user_id' => $user_id]);
$recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Evolução dos últimos 6 meses (alimenta o gráfico do Chart.js)
$stmt = $pdo->prepare('SELECT MONTH(date) AS month, YEAR(date) AS year, SUM(amount) AS total FROM expenses WHERE user_id = :user_id AND date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY YEAR(date), MONTH(date) ORDER BY YEAR(date), MONTH(date)');
$stmt->execute([':user_id' => $user_id]);
$monthly_evolution = $stmt->fetchAll(PDO::FETCH_ASSOC);
