/**
 * dashboard.js
 *
 * Inicializacao do grafico de evolucao de gastos (Chart.js).
 *
 * Depende de:
 *  - Chart.js carregado via CDN na view (dashboard.php)
 *  - Variavel global DASHBOARD_DATA definida na view antes deste script,
 *    com o shape: { monthlyEvolution: [{ month: 1, total: 123.45 }, ...] }
 */

const ctx = document.getElementById('expenseChart').getContext('2d');
const monthlyData = DASHBOARD_DATA.monthlyEvolution;

const labels = monthlyData.map(item => {
    const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    return months[item.month - 1];
});

const data = monthlyData.map(item => item.total);

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Gastos',
            data: data,
            backgroundColor: '#7C3AED',
            borderRadius: 8,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: { grid: { color: '#2E2E4E' }, ticks: { color: '#9090A0' } },
            y: { grid: { color: '#2E2E4E' }, ticks: { color: '#9090A0' } }
        }
    }
});
