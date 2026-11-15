/**
 * Vesper — Graphiques dynamiques (Chart.js).
 * Initialise les <canvas data-chart="line|doughnut|bar"> présents dans le DOM.
 * Données passées via attributs data-labels / data-values (JSON).
 */
import Chart from 'chart.js/auto';

const PALETTE = ['#32936f', '#2274a5', '#ffbf00', '#e83f6f', '#14b8a6', '#6366f1'];

export function initCharts() {
    document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        if (canvas.dataset.initialized) return;
        canvas.dataset.initialized = '1';

        const kind = canvas.dataset.chart || 'doughnut';
        const label = canvas.dataset.label || '';
        const labels = safeParse(canvas.dataset.labels);
        const values = safeParse(canvas.dataset.values);

        const isLine = kind === 'line' || kind === 'area';
        const isDoughnut = kind === 'doughnut';

        new Chart(canvas, {
            type: isLine ? 'line' : isDoughnut ? 'doughnut' : 'bar',
            data: {
                labels,
                datasets: [{
                    label,
                    data: values,
                    backgroundColor: isDoughnut
                        ? PALETTE
                        : (context) => {
                            const { chart } = context;
                            const { ctx, chartArea } = chart;
                            if (!chartArea) return 'rgba(50, 147, 111, 0.15)';
                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, 'rgba(50, 147, 111, 0.35)');
                            gradient.addColorStop(1, 'rgba(50, 147, 111, 0.02)');
                            return gradient;
                        },
                    borderColor: isDoughnut ? 'rgba(255,255,255,0.7)' : '#32936f',
                    borderWidth: isDoughnut ? 2 : 3,
                    fill: isLine,
                    tension: 0.4,
                    pointRadius: isLine ? 4 : 0,
                    pointBackgroundColor: '#32936f',
                    pointBorderColor: '#ffffff',
                    pointHoverRadius: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: isDoughnut, position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                },
                scales: isDoughnut ? {} : {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,0.15)' }, ticks: { font: { size: 10 } } },
                },
            },
        });
    });
}

function safeParse(value) {
    try {
        const parsed = JSON.parse(value || '[]');
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}
