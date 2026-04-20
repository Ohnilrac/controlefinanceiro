<?php
/**
 * sidebar.php
 *
 * Componente reutilizavel da barra lateral do painel.
 * Usado pelas paginas internas: dashboard, expenses, categories, settings.
 *
 * Como usar (a partir de uma pagina dentro de /pages):
 *   $active_page = 'dashboard';
 *   include __DIR__ . '/../includes/sidebar.php';
 *
 * Valores aceitos para $active_page:
 *   'dashboard' | 'expenses' | 'categories' | 'settings'
 *
 * Se $active_page nao for definido, nenhum item recebe destaque.
 */

// O ?? '' garante que $active_page sempre tem valor — evita warning
// caso a variavel nao tenha sido definida pela pagina que incluiu o sidebar.
$active_page = $active_page ?? '';
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <span class="logo-icon">💰</span>
        <span class="logo-text">Controle<br>Financeiro</span>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?php echo $active_page === 'dashboard' ? 'active' : ''; ?>">
            <span class="nav-icon">🏠</span>
            <span>Dashboard</span>
        </a>
        <a href="expenses.php" class="nav-item <?php echo $active_page === 'expenses' ? 'active' : ''; ?>">
            <span class="nav-icon">📋</span>
            <span>Meus Gastos</span>
        </a>
        <a href="categories.php" class="nav-item <?php echo $active_page === 'categories' ? 'active' : ''; ?>">
            <span class="nav-icon">🏷️</span>
            <span>Categorias</span>
        </a>

        <div class="nav-divider"></div>

        <a href="settings.php" class="nav-item <?php echo $active_page === 'settings' ? 'active' : ''; ?>">
            <span class="nav-icon">⚙️</span>
            <span>Configurações</span>
        </a>
    </nav>

    <a href="logout.php" class="sidebar-logout">
        <span>🚪</span>
        <span>Sair</span>
    </a>
</aside>
