/**
 * expenses.js
 *
 * Comportamento específico do modal de adicionar/editar gasto.
 *
 * openModal() e closeModalOverlay() vêm de modal.js,
 * carregado globalmente pelo header.php.
 *
 * closeModal(event) é declarado aqui pois, além de fechar o overlay,
 * precisa limpar o formulário e remover o parâmetro ?edit= da URL.
 */

/**
 * Fecha o modal e restaura o estado inicial do formulário.
 * Chamado pelo botão X, pelo botão Cancelar e pelo clique no overlay.
 *
 * @param {Event|undefined} event
 */
function closeModal(event) {
    // closeModalOverlay cuida de verificar se deve fechar e remove .active
    if (closeModalOverlay(event)) {

        // Remove o parâmetro ?edit=... da URL para que um F5
        // não reabra o modal de edição involuntariamente
        var url = new URL(window.location);
        url.searchParams.delete('edit');
        window.history.replaceState({}, '', url);

        // Reseta todos os campos do formulário (nome, data, checkbox, textarea...)
        document.querySelector('.modal form').reset();

        // Remove o input hidden de expense_id (presente só no modo edição)
        var expenseIdInput = document.querySelector('input[name="expense_id"]');
        if (expenseIdInput) expenseIdInput.remove();

        // Restaura o título do modal para o estado padrão
        document.querySelector('.modal h2').textContent = 'Adicionar Gasto';

        // Limpa o campo de valor (o .reset() pode deixar a máscara residual)
        var amountInput = document.getElementById('amountInput');
        if (amountInput) amountInput.value = '';
    }
}

// Abre o modal automaticamente se a view indicou modo de edição
// (usuário clicou em "Editar" e a página recarregou com ?edit=ID)
if (typeof EXPENSE_EDIT_MODE !== 'undefined' && EXPENSE_EDIT_MODE) {
    openModal();
}
