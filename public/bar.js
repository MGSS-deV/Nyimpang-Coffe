// ==========================================
// NYIMPANG COFFEE - DASHBOARD BARISTA (REALTIME)
// ==========================================

let orders = [];
let audioEnabled = true;

const socket = io();

// ---------- NOTIFIKASI SUARA "TRING" ----------
function playTringSound() {
    if (!audioEnabled) return;
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();

        const osc1 = ctx.createOscillator();
        const gain1 = ctx.createGain();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(1318.51, ctx.currentTime); // E6
        gain1.gain.setValueAtTime(0.3, ctx.currentTime);
        gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
        osc1.connect(gain1);
        gain1.connect(ctx.destination);
        osc1.start();
        osc1.stop(ctx.currentTime + 0.35);

        setTimeout(() => {
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(1760, ctx.currentTime); // A6
            gain2.gain.setValueAtTime(0.35, ctx.currentTime);
            gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start();
            osc2.stop(ctx.currentTime + 0.5);
        }, 120);
    } catch (e) {
        console.warn('Audio Context belum diaktifkan user.');
    }
}

function enableAudio() {
    audioEnabled = !audioEnabled;
    const btn = document.getElementById('audio-btn');
    if (btn) btn.innerText = audioEnabled ? '🔊 Suara Aktif' : '🔇 Suara Mati';
    if (audioEnabled) playTringSound();
}

// ---------- LOAD AWAL ----------
async function loadInitialOrders() {
    try {
        const response = await fetch('/api/orders');
        const data = await response.json();
        if (data.success) {
            orders = data.orders;
            updateStats(data.stats);
            renderBoard();
        }
    } catch (error) {
        console.error('[BARISTA ERROR] Gagal memuat pesanan awal:', error);
    }
}

// ---------- REALTIME: PESANAN BARU ----------
socket.on('pesanan-baru', (payload) => {
    const { order, stats } = payload;
    orders.unshift(order);
    playTringSound();
    updateStats(stats);
    renderBoard();
});

// ---------- REALTIME: STATUS DIPERBARUI ----------
socket.on('status-diperbarui', (payload) => {
    const { order, stats } = payload;
    const idx = orders.findIndex(o => o.id === order.id);
    if (idx !== -1) orders[idx] = order;
    updateStats(stats);
    renderBoard();
});

// ---------- STATISTIK ----------
function updateStats(stats) {
    if (!stats) return;
    const revEl = document.getElementById('stat-revenue');
    const pendEl = document.getElementById('stat-pending');
    const compEl = document.getElementById('stat-completed');
    const totEl = document.getElementById('stat-total');

    if (revEl) revEl.innerText = `Rp ${stats.totalRevenue.toLocaleString('id-ID')}`;
    if (pendEl) pendEl.innerText = stats.pendingOrders;
    if (compEl) compEl.innerText = stats.completedOrders;
    if (totEl) totEl.innerText = stats.totalOrders;
}

// ---------- PAPAN KANBAN 3 KOLOM ----------
function renderBoard() {
    const active = orders.filter(o => o.status !== 'Selesai' && o.status !== 'Dibatalkan');

    const kolomMasuk = active.filter(o => o.status === 'Masuk');
    const kolomDibuat = active.filter(o => o.status === 'Dibuat');
    const kolomSiap = active.filter(o => o.status === 'Siap Diambil');

    document.getElementById('count-masuk').innerText = kolomMasuk.length;
    document.getElementById('count-dibuat').innerText = kolomDibuat.length;
    document.getElementById('count-siap').innerText = kolomSiap.length;

    renderColumn('col-masuk', kolomMasuk, { label: 'Mulai Dibuat', nextStatus: 'Dibuat' });
    renderColumn('col-dibuat', kolomDibuat, { label: 'Siap Diambil', nextStatus: 'Siap Diambil' });
    renderColumn('col-siap', kolomSiap, { label: 'Sudah Diambil', nextStatus: 'Selesai' });
}

function renderColumn(containerId, columnOrders, action) {
    const container = document.getElementById(containerId);
    if (!container) return;

    if (columnOrders.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 rounded-[var(--radius-md)]" style="border: 1px dashed var(--border)">
                <p class="text-xs" style="color: var(--text-faint)">Tidak ada pesanan.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = columnOrders.map(order => `
        <div class="surface-card p-4">
            <div class="flex justify-between items-start pb-2 mb-2 hairline-divider pt-0">
                <div>
                    <span class="text-[10px] block" style="color: var(--text-faint)">${order.id} • ${order.createdAt}</span>
                    <h3 class="text-sm font-medium mt-0.5" style="color: var(--text)">${order.customerName} <span class="text-[11px]" style="color: var(--text-muted)">(${order.orderType})</span></h3>
                </div>
            </div>

            <div class="flex justify-between text-[11px] px-2.5 py-1.5 rounded-[var(--radius-sm)] mb-2" style="background: var(--bg); color: var(--text-muted)">
                <span>Meja <strong style="color: var(--text)">${order.tableNo}</strong></span>
                <span>${order.paymentMethod}</span>
            </div>

            <ul class="mb-3 space-y-1">
                ${order.items.map(item => `
                    <li class="flex justify-between text-xs">
                        <span style="color: var(--text)">${item.name} <span style="color: var(--accent-dark)">x${item.qty}</span></span>
                        <span style="color: var(--text-muted)">Rp ${(item.price * item.qty).toLocaleString('id-ID')}</span>
                    </li>
                `).join('')}
            </ul>

            <div class="flex justify-between items-center mb-3 pt-2 hairline-divider">
                <span class="text-[11px]" style="color: var(--text-muted)">Total</span>
                <span class="text-sm font-semibold" style="color: var(--accent-dark)">Rp ${order.totalAmount.toLocaleString('id-ID')}</span>
            </div>

            <div class="flex items-center gap-2">
                ${order.status === 'Masuk' ? `
                    <button onclick="updateOrderStatus('${order.id}', 'Dibatalkan')" class="btn-text-muted text-xs py-2 px-1 cursor-pointer">
                        Batalkan
                    </button>
                ` : ''}
                <button onclick="updateOrderStatus('${order.id}', '${action.nextStatus}')" class="btn-primary flex-1 text-xs py-2.5 cursor-pointer">
                    ${action.label}
                </button>
            </div>
        </div>
    `).join('');
}

// ---------- KIRIM PERUBAHAN STATUS ----------
async function updateOrderStatus(orderId, newStatus) {
    try {
        const response = await fetch(`/api/orders/${orderId}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: newStatus })
        });
        const result = await response.json();
        if (!result.success) alert('Gagal memperbarui status: ' + result.message);
        // Render terjadi lewat broadcast 'status-diperbarui' dari server.
    } catch (error) {
        console.error('[BARISTA ERROR]', error);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadInitialOrders();
});
