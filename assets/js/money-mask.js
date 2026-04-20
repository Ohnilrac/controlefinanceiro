/**
 * money-mask.js
 *
 * Mascara de moeda brasileira (R$ 0.000,00) aplicada automaticamente a
 * qualquer <input> com a classe ".money-input".
 *
 * Comportamento:
 *  - Ao digitar: formata o valor para o padrao R$ X.XXX,XX
 *  - Ao pressionar tecla: bloqueia caracteres nao numericos (exceto
 *    Backspace, Delete, setas e Tab)
 *  - Antes de submeter o formulario: remove a mascara, deixando apenas
 *    o numero (ex: "1234.56") para o PHP converter em float sem ambiguidade
 *
 * Carregado globalmente pelo footer.php. Funciona em qualquer pagina;
 * se nao houver input ".money-input", simplesmente nao faz nada.
 */

document.querySelectorAll('.money-input').forEach(function(input) {
    // Formata o valor a cada digitacao
    input.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        value = (parseInt(value || '0') / 100).toFixed(2);
        this.value = 'R$ ' + value.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    });

    // Bloqueia entrada de caracteres nao numericos
    input.addEventListener('keydown', function(e) {
        const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
        if (!/[0-9]/.test(e.key) && !allowedKeys.includes(e.key)) {
            e.preventDefault();
        }
    });

    // Remove a mascara antes do submit para o PHP receber um numero puro
    const form = input.closest('form');
    if (form) {
        form.addEventListener('submit', function() {
            input.value = input.value
                .replace('R$ ', '')
                .replace(/\./g, '')
                .replace(',', '.');
        });
    }
});
