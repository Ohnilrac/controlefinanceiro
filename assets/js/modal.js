/**
 * modal.js
 *
 * Utilitário base de modal, carregado globalmente pelo header.php.
 * Toda página que tenha um <div id="modalOverlay"> herda essas funções.
 *
 * Funções exportadas:
 *  - openModal()              → adiciona .active ao #modalOverlay
 *  - closeModalOverlay(event) → remove .active se o clique foi no overlay
 *                               ou se chamado sem evento (botão X).
 *                               Retorna true se o modal foi de fato fechado.
 *
 * Uso nos arquivos de página (expenses.js, categories.js etc.):
 *  - Chamar openModal() diretamente (vem daqui).
 *  - Declarar closeModal(event) na página, chamando closeModalOverlay(event)
 *    internamente para o comportamento base, e adicionar lógica específica.
 */

function openModal() {
    var overlay = document.getElementById('modalOverlay');
    if (overlay) overlay.classList.add('active');
}

/**
 * Fecha o modal se o evento vier do próprio overlay (clique fora)
 * ou se nenhum evento for passado (botão X, Cancelar).
 *
 * @param {Event|undefined} event - Evento do clique (opcional)
 * @returns {boolean} true se o modal foi fechado, false caso contrário
 */
function closeModalOverlay(event) {
    var overlay = document.getElementById('modalOverlay');
    if (!overlay) return false;
    if (!event || event.target === overlay) {
        overlay.classList.remove('active');
        return true;
    }
    return false;
}
