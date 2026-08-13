// ==========================================
// NYIMPANG COFFEE - DASHBOARD RINGKASAN/ANALITIK (FITUR BARU)
// ==========================================

let salesChart = null;
let hoursChart = null;

// ---------- SESI LOGIN (pola sama dengan halaman staff lain) ----------
async function loadStaffInfo() {
    try {
        const response = await fetch('/api/auth_me.php');
        const data = await response.json();
        const badge = document.getElementById('staff-badge');
        if (data.success && badge) {
            badge.innerText = `👤 ${data.user.username} (${data.user.role})`;
        }
        const menuLink = document.getElementById('nav-menu-link');
        if (menuLink && data.success && data.user.role === 'Admin') {
            menuLink.classList.remove('hidden');
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

// ---------- MUAT DATA ANALITIK ----------
async function loadAnalytics(days = 7) {
    try {
        const response = await authFetch(`/api/analytics_summary.php?days=${days}`);
        const data = await response.json();
        if (!data.success) return;

        document.getElementById('stat-range-revenue').innerText = `Rp ${data.summary.totalRevenue.toLocaleString('id-ID')}`;
        document.getElementById('stat-range-orders').innerText = data.summary.totalOrders;
        document.getElementById('stat-range-avg').innerText = `Rp ${data.summary.avgPerOrder.toLocaleString('id-ID')}`;

        renderSalesChart(data.dailySales);
        renderHoursChart(data.busiestHours);
        renderTopItems(data.topItems);
    } catch (error) {
        console.error('[DASHBOARD ERROR] Gagal memuat analitik:', error);
    }
}

function renderSalesChart(dailySales) {
    const canvas = document.getElementById('sales-chart');
    if (!canvas || typeof Chart === 'undefined') return;

    const labels = dailySales.map(d => d.label);
    const totals = dailySales.map(d => d.total);

    if (salesChart) salesChart.destroy();
    salesChart = new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Penjualan',
                data: totals,
                borderColor: '#A8613A',
                backgroundColor: 'rgba(168, 97, 58, 0.12)',
                tension: 0.3,
                fill: true,
                pointRadius: 3,
                pointBackgroundColor: '#7C4526'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `Rp ${ctx.parsed.y.toLocaleString('id-ID')}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID') }
                }
            }
        }
    });
}

function renderHoursChart(busiestHours) {
    const canvas = document.getElementById('hours-chart');
    if (!canvas || typeof Chart === 'undefined') return;

    const labels = busiestHours.map(h => h.hour);
    const counts = busiestHours.map(h => h.orders);

    if (hoursChart) hoursChart.destroy();
    hoursChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Jumlah Pesanan',
                data: counts,
                backgroundColor: '#DCC0AA',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

function renderTopItems(topItems) {
    const container = document.getElementById('top-items-list');
    if (!container) return;

    if (topItems.length === 0) {
        container.innerHTML = `<p class="text-xs py-8 text-center" style="color: var(--text-faint)">Belum ada data penjualan pada rentang ini.</p>`;
        return;
    }

    const maxQty = Math.max(...topItems.map(i => i.qty), 1);

    container.innerHTML = topItems.map((item, idx) => `
        <div class="mb-4 last:mb-0">
            <div class="flex justify-between text-xs mb-1.5">
                <span style="color: var(--text)">${idx + 1}. ${item.name}</span>
                <span style="color: var(--text-muted)">${item.qty} terjual</span>
            </div>
            <div class="w-full h-2 rounded-full" style="background: var(--border-soft)">
                <div class="h-2 rounded-full" style="width: ${(item.qty / maxQty) * 100}%; background: var(--accent)"></div>
            </div>
        </div>
    `).join('');
}

// ---------- INISIALISASI ----------
document.addEventListener('DOMContentLoaded', () => {
    loadStaffInfo();
    loadAnalytics(7);

    document.querySelectorAll('.range-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.range-btn').forEach(b => b.classList.remove('range-btn-active'));
            btn.classList.add('range-btn-active');
            loadAnalytics(Number(btn.dataset.days));
        });
    });
});
