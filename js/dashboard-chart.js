document.addEventListener('DOMContentLoaded', function () {
    const chartEl = document.getElementById('revenue-chart');
    if (!chartEl || !window.Chart) {
        return;
    }

    let labels;
    let data;
    try {
        labels = JSON.parse(chartEl.dataset.labels || '[]');
        data = JSON.parse(chartEl.dataset.values || '[]');
    } catch (error) {
        return;
    }

    new Chart(chartEl, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue',
                data: data,
                borderColor: '#d9480f',
                backgroundColor: 'rgba(217, 72, 15, 0.08)',
                tension: 0.3,
                fill: true,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
