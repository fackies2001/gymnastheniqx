// 📁 resources/js/dashboard.js
// 🏷️ JAVASCRIPT - Complete Fixed Version
// 📍 REPLACE THE ENTIRE FILE WITH THIS

(function() {
    'use strict';

    // ============================================================
    // ✅ DARK MODE
    // ============================================================
    const DarkMode = {
        isEnabled() {
            return localStorage.getItem('darkMode') === 'enabled';
        },

        enable() {
            document.body.classList.add('dark-mode');
            localStorage.setItem('darkMode', 'enabled');
            this._setIcon(true);
            console.log('[DarkMode] ON ✅');
        },

        disable() {
            document.body.classList.remove('dark-mode');
            localStorage.setItem('darkMode', 'disabled');
            this._setIcon(false);
            console.log('[DarkMode] OFF ✅');
        },

        _setIcon(isDark) {
            const icon = document.getElementById('darkModeIcon');
            if (!icon) return;
            icon.classList.toggle('fa-sun',  isDark);
            icon.classList.toggle('fa-moon', !isDark);
        },

        init() {
            // Apply stored preference immediately
            if (this.isEnabled()) {
                document.body.classList.add('dark-mode');
                this._setIcon(true);
            } else {
                this._setIcon(false);
            }

            const btn = document.getElementById('darkModeToggle');
            if (!btn) {
                console.warn('[DarkMode] Toggle button not found!');
                return;
            }

            // ✅ Guard: prevent duplicate event binding on Vite HMR re-runs
            if (btn.dataset.darkModeInit === '1') {
                console.log('[DarkMode] Already initialized, skipping.');
                return;
            }
            btn.dataset.darkModeInit = '1';

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.isEnabled() ? this.disable() : this.enable();
            });

            console.log('[DarkMode] Initialized ✅');
        }
    };

    // ============================================================
    // ✅ CHART HELPERS
    // ============================================================
    function getChartLabels(data) {
        if (!data) return [];
        // ✅ UPDATED: Now checks for status_name first
        if (Array.isArray(data)) return data.map(item => item.status_name || item.name || item.product_status_name || 'Unknown');
        return Object.keys(data);
    }

    function getChartValues(data) {
        if (!data) return [];
        if (Array.isArray(data)) return data.map(item => parseInt(item.total ?? item.count ?? 0) || 0);
        return Object.values(data).map(val => parseInt(val) || 0);
    }

    function hasData(labels, values) {
        return labels.length > 0 && values.length > 0 && values.some(v => v > 0);
    }

    function showNoData(canvasEl) {
        const wrapper = canvasEl.parentElement;
        wrapper.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="fas fa-chart-pie mb-2" style="font-size:2rem; opacity:0.3; display:block;"></i>
                <span style="font-size:0.85rem;">No data available</span>
            </div>`;
    }

    // ============================================================
    // ✅ CHART 1: Purchase Request Status (Doughnut)
    // ============================================================
    function initPurchaseRequestChart() {
        const el = document.getElementById('monthlyProductReceived');
        if (!el) return;

        const data   = window.purchase_request_status_counts;
        const labels = getChartLabels(data);
        const values = getChartValues(data);

        if (!hasData(labels, values)) { showNoData(el); return; }

        new Chart(el.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: ['#FFCD56','#36A2EB','#FF6384','#4BC0C0','#9966FF','#FF9F40'],
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        display: true, 
                        position: 'right',
                        labels: {
                            padding: 15,
                            font: { size: 12 },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    
                                    return {
                                        text: `${label}: ${value} (${percentage}%)`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label(ctx) {
                                const total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                                const pct   = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${ctx.raw} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // ============================================================
    // ✅ CHART 2: Product Status (Doughnut)
    // ============================================================
    function initProductStatusChart() {
        const el = document.getElementById('monthlyProductRelease');
        if (!el) return;

        const data   = window.product_status_counts;
        const labels = getChartLabels(data);
        const values = getChartValues(data);

        if (!hasData(labels, values)) { showNoData(el); return; }

        new Chart(el.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: ['#4BC0C0','#FFCD56','#FF6384','#C9CBCF','#36A2EB','#9966FF'],
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        display: true, 
                        position: 'right',
                        labels: {
                            padding: 15,
                            font: { size: 12 },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    
                                    return {
                                        text: `${label}: ${value} (${percentage}%)`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label(ctx) {
                                const total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                                const pct   = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${ctx.raw} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // ============================================================
    // ✅ CHART: Monthly Sales / Income (Bar) + sort + highlight
    // ============================================================
    const MONTH_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    const monthlySalesState = {
        chart: null,
        year: new Date().getFullYear(),
        chronicOrder: true,
        highlightIndex: null,
        origLabels: [],
        origData: [],
    };

    function defaultSalesColors(highlightIdx) {
        const base = 'rgba(40, 167, 69, 0.75)';
        const hi = 'rgba(255, 193, 7, 0.95)';
        const len = monthlySalesState.chart
            ? monthlySalesState.chart.data.datasets[0].data.length
            : monthlySalesState.origData.length;
        return Array.from({ length: len }, (_, i) => {
            let origIdx = i;
            if (!monthlySalesState.chronicOrder && monthlySalesState._sortMap) {
                origIdx = monthlySalesState._sortMap[i];
            }
            const isHi = highlightIdx !== null && highlightIdx !== undefined && origIdx === highlightIdx;
            return isHi ? hi : base;
        });
    }

    function applySalesChartHighlight(idx) {
        monthlySalesState.highlightIndex = idx;
        if (!monthlySalesState.chart) return;
        const ds = monthlySalesState.chart.data.datasets[0];
        ds.backgroundColor = defaultSalesColors(idx);
        monthlySalesState.chart.update('none');
    }

    function scrollToSalesChart() {
        const el = document.getElementById('dashboard-monthly-sales-card');
        el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    window.highlightDashboardSalesMonth = function (monthIndex) {
        const idx = parseInt(monthIndex, 10);
        if (Number.isNaN(idx) || idx < 0 || idx > 11) return;
        scrollToSalesChart();
        setTimeout(() => applySalesChartHighlight(idx), 350);
    };

    function reorderSalesChart(byAmount) {
        if (!monthlySalesState.chart) return;

        if (!byAmount) {
            monthlySalesState.chronicOrder = true;
            monthlySalesState._sortMap = null;
            monthlySalesState.chart.data.labels = [...monthlySalesState.origLabels];
            monthlySalesState.chart.data.datasets[0].data = [...monthlySalesState.origData];
        } else {
            monthlySalesState.chronicOrder = false;
            const pairs = monthlySalesState.origLabels.map((label, i) => ({
                label,
                value: monthlySalesState.origData[i],
                origIdx: i,
            }));
            pairs.sort((a, b) => b.value - a.value);
            monthlySalesState._sortMap = pairs.map(p => p.origIdx);
            monthlySalesState.chart.data.labels = pairs.map(p => p.label);
            monthlySalesState.chart.data.datasets[0].data = pairs.map(p => p.value);
        }

        monthlySalesState.chart.data.datasets[0].backgroundColor = defaultSalesColors(monthlySalesState.highlightIndex);
        monthlySalesState.chart.update();
    }

    function initMonthlySalesIncomeChart() {
        const el = document.getElementById('MonthlySalesIncomeChart');
        if (!el || !window.monthly_sales_income) return;

        const pack = window.monthly_sales_income;
        const amounts = Array.isArray(pack.amounts) ? pack.amounts : [];
        if (amounts.length !== 12) return;

        monthlySalesState.year = pack.year || new Date().getFullYear();
        monthlySalesState.origLabels = MONTH_SHORT.map(l => l);
        monthlySalesState.origData = amounts.map(v => parseFloat(v) || 0);

        if (!monthlySalesState.origData.some(v => v > 0)) {
            showNoData(el);
            return;
        }

        monthlySalesState.chart = new Chart(el.getContext('2d'), {
            type: 'bar',
            data: {
                labels: [...monthlySalesState.origLabels],
                datasets: [{
                    label: `Sales (₱) ${monthlySalesState.year}`,
                    data: [...monthlySalesState.origData],
                    backgroundColor: defaultSalesColors(null),
                    borderColor: 'rgba(25, 135, 84, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback(v) {
                                return '₱' + Number(v).toLocaleString();
                            },
                        },
                        grid: { color: 'rgba(0,0,0,0.06)' },
                    },
                    x: { grid: { display: false } },
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label(ctx) {
                                const n = ctx.raw ?? 0;
                                return ' ₱' + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            },
                        },
                    },
                },
            },
        });

        const btnChrono = document.getElementById('salesSortChrono');
        const btnAmount = document.getElementById('salesSortAmount');
        if (btnChrono && btnAmount) {
            btnChrono.addEventListener('click', () => {
                btnChrono.classList.add('active');
                btnAmount.classList.remove('active');
                reorderSalesChart(false);
            });
            btnAmount.addEventListener('click', () => {
                btnAmount.classList.add('active');
                btnChrono.classList.remove('active');
                reorderSalesChart(true);
            });
        }

        document.querySelectorAll('.activity-jump-sales').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const m = btn.getAttribute('data-sales-month');
                window.highlightDashboardSalesMonth(m);
            });
        });
    }

    // ============================================================
    // ✅ CHART 3: Monthly Products Scanned (Bar)
    // ============================================================
    function initMonthlyBarChart() {
        const el = document.getElementById('MonthlyProductsScanned');
        if (!el) return;

        const rawData = window.monthly_products_in;
        if (!rawData) { showNoData(el); return; }

        const monthlyArr = Array(12).fill(0);

        if (Array.isArray(rawData)) {
            rawData.forEach((val, idx) => { monthlyArr[idx] = parseInt(val) || 0; });
        } else {
            Object.entries(rawData).forEach(([month, count]) => {
                const idx = parseInt(month) - 1;
                if (idx >= 0 && idx < 12) monthlyArr[idx] = parseInt(count) || 0;
            });
        }

        if (!monthlyArr.some(v => v > 0)) { showNoData(el); return; }

        new Chart(el.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [{
                    label: 'Products Scanned',
                    backgroundColor: 'rgba(102,126,234,0.7)',
                    borderColor: '#667eea',
                    borderWidth: 1,
                    borderRadius: 4,
                    data: monthlyArr,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.raw} product${ctx.raw !== 1 ? 's' : ''} scanned`
                        }
                    }
                }
            }
        });
    }

    // ============================================================
    // ✅ INIT ALL
    // ============================================================
    function init() {
        DarkMode.init();
        initPurchaseRequestChart();
        initProductStatusChart();
        initMonthlySalesIncomeChart();
        initMonthlyBarChart();
        console.log('✅ Dashboard.js initialized!');
    }

    document.readyState === 'loading'
        ? document.addEventListener('DOMContentLoaded', init)
        : init();

})();