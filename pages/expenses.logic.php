<?php
/**
 * expenses.logic.php
 *
 * Camada de lógica da página de gastos (CRUD completo).
 *
 * Responsabilidades:
 *  - Iniciar sessão e incluir PDO
 *  - Garantir usuário autenticado
 *  - Carregar dados do usuário e calcular range de anos para os filtros
 *  - Aplicar filtros (busca, categoria, mês, ano) na listagem
 *  - Tratar exclusão de despesa (via GET ?delete=ID)
 *  - Tratar inserção e edição de despesa (POST)
 *  - Carregar uma despesa específica para edição (via GET ?edit=ID)
 *
 * Variáveis exportadas para a view (expenses.php):
 *  - $user (array)
 *  - $min_year (int): ano mínimo para o filtro de ano
 *  - $filter_category, $filter_month, $filter_year, $search (filtros atuais)
 *  - $categories (array): categorias do usuário (para selects)
 *  - $expenses (array): gastos da página atual
 *  - $total_records (int): total de gastos com os filtros aplicados
 *  - $total_pages (int): total de páginas
 *  - $current_page (int): página sendo exibida
 *  - $per_page (int): gastos por página
 *  - $action_error, $action_success (string)
 *  - $edit_expense (array|null): despesa carregada para edição, se aplicável
 */

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Carrega dados do usuário (precisamos da data de criação para o range de anos)
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Define o ano mínimo: 2 anos antes da criação da conta ou 2 anos atrás (o que for menor)
$account_year = date('Y', strtotime($user['created_at']));
$min_year     = min($account_year, date('Y') - 2);

// Filtros vindos da URL (GET) com valores padrão (mês e ano atuais)
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';
$filter_month    = isset($_GET['month']) ? $_GET['month'] : date('m');
$filter_year     = isset($_GET['year']) ? $_GET['year'] : date('Y');
$search          = isset($_GET['search']) ? trim($_GET['search']) : '';

// Carrega todas as categorias do usuário (para os selects de filtro e modal)
$stmt = $pdo->prepare('SELECT * FROM categories WHERE user_id = :user_id ORDER BY name');
$stmt->execute([':user_id' => $user_id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Monta a cláusula WHERE dinamicamente conforme os filtros aplicados.
// Separar o WHERE numa variável permite reutilizá-lo na query de contagem
// (COUNT) e na query principal (SELECT), evitando duplicação de lógica.
// Usar prepared statements + bindings evita SQL Injection mesmo
// quando o SQL é construído por concatenação.
$where  = 'WHERE e.user_id = :user_id';
$params = [':user_id' => $user_id];

if (!empty($filter_category)) {
    $where .= ' AND e.category_id = :category_id';
    $params[':category_id'] = $filter_category;
}
if (!empty($filter_month) && !empty($filter_year)) {
    $where .= ' AND MONTH(e.date) = :month AND YEAR(e.date) = :year';
    $params[':month'] = $filter_month;
    $params[':year']  = $filter_year;
}
if (!empty($search)) {
    $where .= ' AND e.name LIKE :search';
    $params[':search'] = '%' . $search . '%';
}

// Conta o total de registros com os filtros aplicados.
// Esse número é necessário para calcular quantas páginas existem.
$stmt = $pdo->prepare('SELECT COUNT(*) FROM expenses e LEFT JOIN categories c ON e.category_id = c.id ' . $where);
$stmt->execute($params);
$total_records = (int) $stmt->fetchColumn();

// Configuração da paginação
$per_page    = 15;                                          // gastos por página
$current_page = max(1, intval($_GET['page'] ?? 1));          // página atual (mínimo 1)
$total_pages  = $total_records > 0 ? (int) ceil($total_records / $per_page) : 1;
$current_page = min($current_page, $total_pages);           // não ultrapassa o total
$offset       = ($current_page - 1) * $per_page;           // ponto de início no banco

// Busca os gastos da página atual com LIMIT e OFFSET.
// LIMIT/OFFSET precisam ser vinculados como inteiros (PDO::PARAM_INT)
// pois o MySQL não aceita valores entre aspas nessas posições.
$sql  = 'SELECT e.*, c.name AS category_name, c.color, c.icon FROM expenses e LEFT JOIN categories c ON e.category_id = c.id ' . $where . ' ORDER BY e.date DESC, e.created_at DESC LIMIT :limit OFFSET :offset';
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit',  $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
$stmt->execute();
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mensagens de feedback para a view
$action_error   = '';
$action_success = '';

// EXCLUIR despesa (via link ?delete=ID)
if (isset($_GET['delete'])) {
    $expense_id = $_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM expenses WHERE id = :id AND user_id = :user_id');
    $stmt->execute([':id' => $expense_id, ':user_id' => $user_id]);
    header('Location: expenses.php');
    exit();
}

// ADICIONAR ou EDITAR despesa (POST do modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // O ?? '' garante valor padrão se a chave não existir no $_POST
    $name         = trim($_POST['name'] ?? '');
    $category_id  = trim($_POST['category_id'] ?? '');
    $amount       = trim($_POST['amount'] ?? '');
    $date         = trim($_POST['date'] ?? '');
    $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
    $comment      = trim($_POST['comment'] ?? '');

    if (empty($name) || empty($amount) || empty($category_id) || empty($date)) {
        $action_error = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        if (!empty($_POST['expense_id'])) {
            // EDIÇÃO de despesa existente
            $stmt = $pdo->prepare('UPDATE expenses SET name = :name, category_id = :category_id, amount = :amount, date = :date, is_recurring = :is_recurring, comment = :comment WHERE id = :id AND user_id = :user_id');
            $stmt->execute([
                ':name'         => $name,
                ':category_id'  => $category_id,
                ':amount'       => $amount,
                ':date'         => $date,
                ':is_recurring' => $is_recurring,
                ':comment'      => $comment,
                ':id'           => $_POST['expense_id'],
                ':user_id'      => $user_id,
            ]);
            $action_success = 'Despesa atualizada com sucesso.';
        } else {
            // INSERÇÃO de nova despesa
            $stmt = $pdo->prepare('INSERT INTO expenses (user_id, name, category_id, amount, date, is_recurring, comment) VALUES (:user_id, :name, :category_id, :amount, :date, :is_recurring, :comment)');
            $stmt->execute([
                ':user_id'      => $user_id,
                ':name'         => $name,
                ':category_id'  => $category_id,
                ':amount'       => $amount,
                ':date'         => $date,
                ':is_recurring' => $is_recurring,
                ':comment'      => $comment,
            ]);
            $action_success = 'Despesa adicionada com sucesso.';
        }
        header('Location: expenses.php');
        exit();
    }
}

// Carrega dados de uma despesa para edição (modal abre preenchido)
$edit_expense = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM expenses WHERE id = :id AND user_id = :user_id');
    $stmt->execute([':id' => $_GET['edit'], ':user_id' => $user_id]);
    $edit_expense = $stmt->fetch(PDO::FETCH_ASSOC);
}
