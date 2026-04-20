/**
 * expenses.js
 *
 * Comportamento do modal de adicionar/editar gasto na pagina "Meus Gastos".
 *
 * A mascara de R$ do campo "amount" e aplicada pelo money-mask.js (via classe
 * .money-input), carregado globalmente pelo footer.php.
 *
 * Depende de:
 *  - Flag global EXPENSE_EDIT_MODE (boolean) definida pela view para indicar
 *    se o modal deve abrir automaticamente em modo de edicao.
 */

const modalOverlay = document.getElementById('modalOverlay');

function openModal() {
    modalOverlay.classList.add('active');
}

function closeModal(event) {
    // Fecha apenas se o clique foi no overlay (fora do modal) ou via botao X
    if (!event || event.target === modalOverlay) {
        modalOverlay.classList.remove('active');

        // Limpa o parametro ?edit=... da URL (caso tenha entrado em modo edicao)
        const url = new URL(window.location);
        url.searchParams.delete('edit');
        window.history.replaceState({}, '', url);

        // Reseta os campos do modal para um novo gasto
        document.querySelector('.modal form').reset();
        const expenseIdInput = document.querySelector('input[name="expense_id"]');
        if (expenseIdInput) expenseIdInput.remove();
        document.querySelector('.modal h2').textContent = 'Adicionar Gasto';

        const amountInput = document.getElementById('amountInput');
        if (amountInput) amountInput.value = '';
    }
}

// Abre o modal automaticamente se a view indicou modo de edicao
if (typeof EXPENSE_EDIT_MODE !== 'undefined' && EXPENSE_EDIT_MODE) {
    openModal();
}
