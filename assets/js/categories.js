/**
 * categories.js
 *
 * Comportamento específico do modal de categorias e do seletor de emoji.
 *
 * openModal() e closeModalOverlay() vêm de modal.js,
 * carregado globalmente pelo header.php.
 */

/**
 * Fecha o modal de categoria.
 * Usa a lógica base do closeModalOverlay (sem limpeza adicional,
 * pois o formulário de categoria não precisa de reset manual).
 *
 * @param {Event|undefined} event
 */
function closeModal(event) {
    closeModalOverlay(event);
}

/**
 * Marca o emoji clicado como selecionado e preenche o input oculto.
 * O parâmetro "emoji" é passado via onclick no HTML.
 *
 * @param {string} emoji - O emoji escolhido pelo usuário
 */
function selectEmoji(emoji) {
    // Preenche o input de ícone com o emoji selecionado
    document.getElementById('iconInput').value = emoji;

    // Remove o destaque de todos os emojis e aplica apenas no clicado
    document.querySelectorAll('.emoji-option').forEach(function(el) {
        el.classList.remove('selected');
    });
    // "event" aqui vem do escopo global do handler onclick="selectEmoji(...)"
    event.target.classList.add('selected');
}

// Abre o modal automaticamente se a view indicou modo de edição
if (typeof CATEGORY_EDIT_MODE !== 'undefined' && CATEGORY_EDIT_MODE) {
    openModal();
}
