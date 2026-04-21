<?php
/**
 * header.php
 *
 * Componente reutilizavel do cabecalho e abertura do layout das paginas internas.
 * Usado pelas paginas: dashboard, expenses, categories, settings.
 *
 * Abre: DOCTYPE, <html>, <head>, <body>, overlay mobile, topbar mobile,
 *       content-wrapper, inclui o sidebar e abre o <main class="main-content">.
 *
 * Variaveis opcionais (definir ANTES do include):
 *   $page_title  => titulo da aba do navegador (default: "Controle Financeiro")
 *   $active_page => usada pelo sidebar.php para destacar o item ativo
 *
 * Como usar (a partir de uma pagina dentro de /pages):
 *   $page_title  = 'Dashboard';
 *   $active_page = 'dashboard';
 *   include __DIR__ . '/../includes/header.php';
 */
$page_title = $page_title ?? 'Controle Financeiro';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Controle Financeiro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Utilitário de modal base (openModal / closeModalOverlay) -->
    <script src="../assets/js/modal.js"></script>
</head>
<body class="dashboard-page">

<!-- OVERLAY (mobile) -->
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
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="main-content">
