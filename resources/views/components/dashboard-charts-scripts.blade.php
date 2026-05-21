{{-- Shared Chart.js Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartDataUrl = @json(route('dashboard.chart-data'));
        const currencyFormat = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(val || 0));
        const shortFormat = (val) => {
            val = Number(val || 0);
            if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(1) + 'M';
            if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(0) + 'jt';
            if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + 'k';
            return 'Rp ' + val;
        };

        const maxWithPadding = (...datasets) => {
            const values = datasets.flat().map((item) => Number(item || 0));
            return Math.max(Math.ceil(Math.max(...values, 1000000) * 1.2), 1000000);
        };

        const budgetChart = new Chart(document.getElementById('budgetChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        label: 'Anggaran (Rencana)',
                        data: {!! json_encode($chartAnggaran) !!},
                        borderColor: '#3b82f6',
                        backgroundColor: 'transparent',
                        tension: 0,
                        pointBackgroundColor: '#3b82f6',
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        borderWidth: 2
                    },
                    {
                        label: 'Realisasi (Aktual)',
                        data: {!! json_encode($chartRealisasi) !!},
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0,
                        pointBackgroundColor: '#10b981',
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, font: { size: 11, family: "'Inter', sans-serif" } } },
                    tooltip: { callbacks: { label: (context) => `${context.dataset.label}: ${currencyFormat(context.parsed.y)}` } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: maxWithPadding({!! json_encode($chartAnggaran) !!}, {!! json_encode($chartRealisasi) !!}),
                        ticks: { callback: shortFormat, font: { size: 10 } },
                        grid: { color: '#f3f4f6', drawBorder: false }
                    },
                    x: { grid: { display: false, drawBorder: false }, ticks: { font: { size: 11 } } }
                }
            }
        });

        const pieLabelsLinePlugin = {
            id: 'pieLabelsLine',
            afterDraw(chart) {
                const { ctx } = chart;
                ctx.save();
                chart.data.datasets.forEach((dataset, datasetIndex) => {
                    const meta = chart.getDatasetMeta(datasetIndex);
                    if (meta.hidden) return;

                    meta.data.forEach((element, index) => {
                        const model = element;
                        const { x, y, startAngle, endAngle, outerRadius } = model;
                        const value = dataset.data[index];
                        
                        // Calculate percentage
                        const total = dataset.data.reduce((sum, val) => sum + (val || 0), 0);
                        if (total === 0 || !value || value === 0) return;
                        const percentage = ((value / total) * 100).toFixed(1) + '%';
                        const label = chart.data.labels[index];

                        // Middle angle of the slice
                        const midAngle = startAngle + (endAngle - startAngle) / 2;
                        const cos = Math.cos(midAngle);
                        const sin = Math.sin(midAngle);

                        // Start point of the line (on the surface/outer boundary of the slice)
                        const startX = x + cos * outerRadius;
                        const startY = y + sin * outerRadius;

                        // Control point/inflection point of the line
                        const lineLength = 22; 
                        const infX = x + cos * (outerRadius + lineLength);
                        const infY = y + sin * (outerRadius + lineLength);

                        // Horizontal line direction
                        const isLeft = cos < 0;
                        const endX = infX + (isLeft ? -40 : 40);
                        const endY = infY;

                        // Draw dot on the slice
                        ctx.beginPath();
                        ctx.arc(startX, startY, 2.5, 0, 2 * Math.PI);
                        ctx.fillStyle = dataset.backgroundColor[index] || '#666';
                        ctx.fill();

                        // Draw line
                        ctx.beginPath();
                        ctx.moveTo(startX, startY);
                        ctx.lineTo(infX, infY);
                        ctx.lineTo(endX, endY);
                        ctx.strokeStyle = '#94a3b8'; // Slate 400
                        ctx.lineWidth = 1;
                        ctx.stroke();

                        // Draw text
                        ctx.font = "bold 12px 'Inter', sans-serif";
                        ctx.fillStyle = "#1e293b"; // Slate 800
                        ctx.textAlign = isLeft ? 'right' : 'left';
                        
                        // Label (above line)
                        ctx.fillText(`${label} (${value})`, endX + (isLeft ? -5 : 5), endY - 4);
                        
                        // Percentage (below line)
                        ctx.font = "normal 11px 'Inter', sans-serif";
                        ctx.fillStyle = "#64748b"; // Slate 500
                        ctx.fillText(percentage, endX + (isLeft ? -5 : 5), endY + 11);
                    });
                });
                ctx.restore();
            }
        };

        const statusChart = new Chart(document.getElementById('statusChart').getContext('2d'), {
            type: 'doughnut',
            plugins: [pieLabelsLinePlugin],
            data: {
                labels: {!! json_encode($statusLabels) !!},
                datasets: [{
                    data: {!! json_encode($statusData) !!},
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 110,
                        right: 110,
                        top: 40,
                        bottom: 40
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });

        const cashflowCanvas = document.getElementById('cashflowChart');
        const cashflowChart = cashflowCanvas ? new Chart(cashflowCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($cfLabels) !!},
                datasets: [
                    {
                        type: 'bar',
                        label: 'Saldo Akhir',
                        data: {!! json_encode($cfBalance) !!},
                        backgroundColor: '#0ea5e9',
                        borderRadius: 2
                    },
                    {
                        type: 'bar',
                        label: 'Uang Masuk',
                        data: {!! json_encode($cfIn) !!},
                        backgroundColor: '#10b981',
                        borderRadius: 2
                    },
                    {
                        type: 'bar',
                        label: 'Uang Keluar',
                        data: {!! json_encode($cfOut) !!},
                        backgroundColor: '#ef4444',
                        borderRadius: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, font: { size: 10 } } },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.dataset.label}: ${currencyFormat(ctx.raw)}` } }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { callback: shortFormat } },
                    x: { grid: { display: false } }
                }
            }
        }) : null;

        const comparisonChart = new Chart(document.getElementById('comparisonChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($comparisonLabels ?? ['3 Bulan', '6 Bulan', '9 Bulan', '12 Bulan']) !!},
                datasets: {!! json_encode($comparisonDatasets ?? []) !!}
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 8, font: { size: 10 } }
                    },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.dataset.label}: ${currencyFormat(ctx.raw)}` } }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { callback: shortFormat } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });

        const setChartData = (chart, labels, datasets) => {
            chart.data.labels = labels || [];
            datasets.forEach((data, index) => {
                chart.data.datasets[index].data = data || [];
            });
            if (chart === budgetChart) {
                chart.options.scales.y.max = maxWithPadding(datasets[0] || [], datasets[1] || []);
            }
            chart.update();
        };

        document.querySelectorAll('[data-dashboard-filter]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const target = form.dataset.dashboardFilter;
                const submitButton = form.querySelector('button[type="submit"]');
                const originalText = submitButton ? submitButton.textContent : '';

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Memuat...';
                }

                try {
                    const params = new URLSearchParams(new FormData(form));
                    const response = await fetch(`${chartDataUrl}?${params.toString()}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });

                    if (!response.ok) throw new Error('Gagal memuat data chart.');

                    const payload = await response.json();

                    if (target === 'budget') {
                        setChartData(budgetChart, payload.budget.labels, [payload.budget.anggaran, payload.budget.realisasi]);
                    }
                    if (target === 'status') {
                        setChartData(statusChart, payload.status.labels, [payload.status.data]);
                    }

                    if (target === 'cashflow' && cashflowChart) {
                        setChartData(cashflowChart, payload.cashflow.labels, [payload.cashflow.balance, payload.cashflow.in, payload.cashflow.out]);
                    }
                    if (target === 'comparison') {
                        comparisonChart.data.labels = payload.comparison.labels || [];
                        comparisonChart.data.datasets = payload.comparison.datasets || [];
                        comparisonChart.update();
                    }
                } catch (error) {
                    console.error(error);
                    alert('Data chart belum bisa dimuat. Silakan coba lagi.');
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                    }
                }
            });
        });
    });
</script>
