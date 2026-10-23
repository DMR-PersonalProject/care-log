import { CategoryScale, Chart, Legend, LinearScale, LineController, LineElement, PointElement, Tooltip } from 'chart.js';

Chart.register(CategoryScale, LinearScale, LineController, LineElement, PointElement, Tooltip, Legend);

// Series colors were checked for color-blind separation; red is kept only for out-of-range points.
const SERIES = ['#2a78d6', '#eb6834'];
const OUT_OF_RANGE = '#d03b3b';
const TEXT = '#44403c';
const GRID = '#e7e5e4';

const CHARTS = {
    'chart-bp': { unit: ' mmHg', series: [['systolic', 'Top number'], ['diastolic', 'Bottom number']] },
    'chart-hr': { unit: ' bpm', series: [['heart_rate', 'Heart rate']] },
    'chart-spo2': { unit: '%', series: [['spo2', 'Oxygen']] },
    'chart-temp': { unit: '°C', series: [['temperature', 'Temperature']] },
};

const dataElement = document.getElementById('vitals-data');
const readings = dataElement ? JSON.parse(dataElement.textContent) : [];

for (const [id, { unit, series }] of Object.entries(CHARTS)) {
    const canvas = document.getElementById(id);
    if (!canvas) continue;

    // Each chart only gets the readings that include its numbers.
    const points = readings.filter((reading) => series.some(([field]) => reading[field] !== null));
    const flagged = (point, field) => Boolean(point.flags[field]);

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: points.map((point) => point.label),
            datasets: series.map(([field, label], i) => ({
                label,
                data: points.map((point) => point[field]),
                borderColor: SERIES[i],
                backgroundColor: SERIES[i],
                borderWidth: 2,
                spanGaps: true,
                pointStyle: points.map((point) => (flagged(point, field) ? 'triangle' : 'circle')),
                pointRadius: points.map((point) => (flagged(point, field) ? 7 : 4)),
                pointHoverRadius: 8,
                pointHitRadius: 12,
                pointBackgroundColor: points.map((point) => (flagged(point, field) ? OUT_OF_RANGE : SERIES[i])),
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
            })),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: series.length > 1,
                    labels: { color: TEXT, font: { size: 15 }, boxWidth: 24, boxHeight: 4 },
                },
                tooltip: {
                    titleFont: { size: 15 },
                    bodyFont: { size: 15 },
                    callbacks: {
                        label(context) {
                            const field = series[context.datasetIndex][0];
                            const flag = points[context.dataIndex].flags[field];
                            return `${context.dataset.label}: ${context.formattedValue}${unit}${flag ? ` (${flag})` : ''}`;
                        },
                    },
                },
            },
            scales: {
                x: { ticks: { color: TEXT, font: { size: 13 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 5 }, grid: { display: false } },
                // grace leaves room so points at the top or bottom are not cut in half.
                y: { grace: '10%', ticks: { color: TEXT, font: { size: 13 }, precision: id === 'chart-temp' ? 1 : 0 }, grid: { color: GRID } },
            },
        },
    });
}
