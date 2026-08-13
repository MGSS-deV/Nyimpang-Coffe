// ==========================================
// NYIMPANG COFFEE - DASHBOARD ANALITIK
// ==========================================

let analyticsData = null;
let salesChart = null;
let topProductsChart = null;
let busyHoursChart = null;
let currentSalesView = 'daily';

// Ambil warna asli dari CSS variables (canvas nggak ngerti var() langsung)
const styles = getComputedStyle(document.documentElement);
const COLOR_ACCENT = styles.getPropertyValue('--accent').trim();
const COLOR_ACCENT_SOFT = styles.getPropertyValue('--accent-soft').trim();
const COLOR_TEXT_MUTED = styles.getPropertyValue('--text-muted').trim();
const COLOR_BORDER = styles.getPropertyValue('--border-soft').trim();

async function loadStaffInfo() {
    try {
        const response = await fetch('/api/auth_me.php');
        const data = await response.json();
        const badge = document.getElementById('staff-badge');
        if (data.success && badge) {
            badge.innerText = `👤 ${data.user.username} (${data.user.role})`;
        }
    } catch (error) {
        console.error('[AUTH] Gagal memuat info staff:', error);
    }
}

async function logout() {
    if (!confirm('Yakin mau keluar?')) return;
    await fetch('/api/auth_logout.php', { method: 'POST' });
    window.location.href = 'login.html';
}

async function authFetch(url, options) {
    const response = await fetch(url, options);
    if (response.status === 401) {
        window.location.href = `login.html?redirect=${encodeURIComponent(window.location.pathname)}`;
        throw new Error('Sesi habis');
    }
    return response;
}

async function loadAnalytics() {
    try {
        const response = await authFetch('/api/dashboard_analytics.php');
        const data = await response.json();
        if (!data.success) return;

        analyticsData = data;
        renderSalesChart();
        renderTopProductsChart(data.topProducts);
        renderBusyHoursChart(data.busiestHours);
    } catch (error) {
        console.error('[DASHBOARD ERROR]', error);
    }
}

function switchSalesView(view) {
    currentSalesView = view;
    document.getElementById('btn-daily').className = view === 'daily' ? 'btn-primary px-3 py-2 text-xs' : 'btn-ghost px-3 py-2 text-xs';
    document.getElementById('btn-weekly').className = view === 'weekly' ? 'btn-primary px-3 py-2 text-xs' : 'btn-ghost px-3 py-2 text-xs';
    renderSalesChart();
}

function renderSalesChart() {
    if (!analyticsData) return;
    const rows = currentSalesView === 'daily' ? analyticsData.dailySales : analyticsData.weeklySales;

    const ctx = document.getElementById('sales-chart');
    if (salesChart) salesChart.destroy();

    salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: rows.map(r => r.label),
            datasets: [{
                label: 'Pemasukan',
                data: rows.map(r => r.revenue),
                borderColor: COLOR_ACCENT,
                backgroundColor: COLOR_ACCENT_SOFT,
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointBackgroundColor: COLOR_ACCENT
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (item) => `Rp ${item.raw.toLocaleString('id-ID')}`
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: (val) => `Rp ${(val / 1000).toLocaleString('id-ID')}rb`,
                        color: COLOR_TEXT_MUTED
                    },
                    grid: { color: COLOR_BORDER }
                },
                x: {
                    ticks: { color: COLOR_TEXT_MUTED },
                    grid: { display: false }
                }
            }
        }
    });
}

function renderTopProductsChart(topProducts) {
    const canvas = document.getElementById('top-products-chart');
    const emptyEl = document.getElementById('top-products-empty');

    if (!topProducts || topProducts.length === 0) {
        canvas.classList.add('hidden');
        emptyEl.classList.remove('hidden');
        return;
    }
    canvas.classList.remove('hidden');
    emptyEl.classList.add('hidden');

    if (topProductsChart) topProductsChart.destroy();
    topProductsChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: topProducts.map(p => p.name),
            datasets: [{
                label: 'Terjual',
                data: topProducts.map(p => p.qty),
                backgroundColor: COLOR_ACCENT,
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (item) => `${item.raw} porsi terjual`
                    }
                }
            },
            scales: {
                x: {
                    ticks: { color: COLOR_TEXT_MUTED, precision: 0 },
                    grid: { color: COLOR_BORDER }
                },
                y: {
                    ticks: { color: COLOR_TEXT_MUTED },
                    grid: { display: false }
                }
            }
        }
    });
}

function renderBusyHoursChart(busiestHours) {
    const ctx = document.getElementById('busy-hours-chart');
    if (busyHoursChart) busyHoursChart.destroy();

    busyHoursChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: busiestHours.map(h => h.label),
            datasets: [{
                label: 'Jumlah Pesanan',
                data: busiestHours.map(h => h.total),
                backgroundColor: COLOR_ACCENT,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (item) => `${item.raw} pesanan`
                    }
                }
            },
            scales: {
                y: {
                    ticks: { color: COLOR_TEXT_MUTED, precision: 0 },
                    grid: { color: COLOR_BORDER }
                },
                x: {
                    ticks: {
                        color: COLOR_TEXT_MUTED,
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 12
                    },
                    grid: { display: false }
                }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    loadStaffInfo();
    loadAnalytics();
});
