/**
 * settings.js
 *
 * Comportamentos especificos da pagina de configuracoes:
 *  - Foco no campo de salario limpa o valor padrao "R$ 0,00" para facilitar
 *    digitacao do primeiro valor
 *  - Re-formata o valor inicial do salario (caso o servidor envie sem a
 *    mascara completa de milhares)
 *
 * A mascara de R$ em si (formatacao ao digitar, bloqueio de letras e remocao
 * antes do submit) vem do money-mask.js — aplicada a todos os inputs com
 * classe .money-input (salario e orcamentos por categoria).
 */

const salaryInput = document.getElementById('salaryInput');

if (salaryInput) {
    // Limpa o valor padrao ao clicar no campo
    salaryInput.addEventListener('focus', function() {
        if (this.value === 'R$ 0,00') {
            this.value = '';
        }
    });

    // Re-formata o valor inicial para garantir separadores de milhar
    if (salaryInput.value && salaryInput.value !== 'R$ 0,00') {
        let value = salaryInput.value.replace(/\D/g, '');
        value = (parseInt(value) / 100).toFixed(2);
        salaryInput.value = 'R$ ' + value.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
}
