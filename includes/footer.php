<?php
/**
 * footer.php
 *
 * Componente reutilizavel do rodape/fechamento do layout das paginas internas.
 * Usado pelas paginas: dashboard, expenses, categories, settings.
 *
 * Carrega os scripts globais (sidebar toggle + mascara de moeda) e fecha as
 * tags <body> e <html>. Deve ser incluido DEPOIS de fechadas as tags </main>
 * e </div> (content-wrapper) da pagina.
 *
 * Como usar (a partir de uma pagina dentro de /pages):
 *   include __DIR__ . '/../includes/footer.php';
 */
?>
<!-- Scripts globais (carregados em todas as paginas autenticadas) -->
<script src="../assets/js/sidebar.js"></script>
<script src="../assets/js/money-mask.js"></script>
</body>
</html>
