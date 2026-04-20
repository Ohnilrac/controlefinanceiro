/**
 * sidebar.js
 *
 * Logica comum da barra lateral no mobile: abrir/fechar ao clicar no hamburguer
 * (topbar) ou no overlay. Carregado globalmente pelo footer.php, portanto
 * disponivel em todas as paginas autenticadas.
 */

function openSidebar() {
    document.querySelector('.sidebar').classList.add('open');
    document.getElementById('overlay').classList.add('active');
}

function closeSidebar() {
    document.querySelector('.sidebar').classList.remove('open');
    document.getElementById('overlay').classList.remove('active');
}
