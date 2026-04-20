/**
 * categories.js
 *
 * Comportamentos da pagina "Categorias":
 *  - Modal de adicionar/editar categoria (abrir/fechar)
 *  - Seletor de emoji (preenche o input oculto ao clicar em um emoji)
 *
 * Depende de:
 *  - Flag global CATEGORY_EDIT_MODE (boolean) definida pela view para indicar
 *    se o modal deve abrir automaticamente em modo de edicao.
 */

const modalOverlay = document.getElementById('modalOverlay');

function openModal() {
    modalOverlay.classList.add('active');
}

function closeModal(event) {
    if (!event || event.target === modalOverlay) {
        modalOverlay.classList.remove('active');
    }
}

function selectEmoji(emoji) {
    document.getElementById('iconInput').value = emoji;
    document.querySelectorAll('.emoji-option').forEach(el => el.classList.remove('selected'));
    // "event" aqui vem do escopo global do handler onclick do <span>
    event.target.classList.add('selected');
}

// Abre o modal automaticamente se a view indicou modo de edicao
if (typeof CATEGORY_EDIT_MODE !== 'undefined' && CATEGORY_EDIT_MODE) {
    openModal();
}
