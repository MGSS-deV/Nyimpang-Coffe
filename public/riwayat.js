// ==========================================
// NYIMPANG COFFEE - RIWAYAT PESANAN
// ==========================================

let currentPage = 1;
let totalPages = 1;

const STATUS_BADGE_STYLE = {
    'Masuk': 'background: var(--accent-soft); color: var(--accent-dark)',
    'Dibuat': 'background: var(--accent-soft); color: var(--accent-dark)',
    'Siap Diambil': 'background: var(--accent); color: white',
    'Selesai': 'background: var(--border-soft); color: var(--text-muted)',
    'Dibatalkan': 'background: #fbe9e7; color: var(--danger)'
};

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

async function loadHistory() {
    const status = document.getElementById('filter-status').value;
    const dateFrom = document.getElementById('filter-date-from').value;
    const dateTo = document.getElementById('filter-date-to').value;

    const params = new URLSearchParams();
    if (status) params.set('status', status);
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo) params.set('date_to', dateTo);
    params.set('page', currentPage);

    try {
        const response = await authFetch(`/api/orders_history.php?${params.toString()}`);
        const data = await response.json();
        if (!data.success) return;

        renderTable(data.orders);
        renderPagination(data.pagination);
    } catch (error) {
        console.error('[RIWAYAT ERROR]', error);
    }
}

function renderTable(orders) {
    const tbody = document.getElementById('history-tbody');
    const emptyEl = document.getElementById('history-empty');

    if (orders.length === 0) {
        tbody.innerHTML = '';
        emptyEl.classList.remove('hidden');
        return;
    }
    emptyEl.classList.add('hidden');

    tbody.innerHTML = orders.map(order => {
        const itemSummary = order.items.map(i => `${i.name} x${i.qty}`).join(', ');
        const badgeStyle = STATUS_BADGE_STYLE[order.status] || '';
        return `
            <tr class="hairline-divider">
                <td class="px-4 py-3" style="color: var(--text-muted)">${order.createdAt}</td>
                <td class="px-4 py-3" style="color: var(--text-faint)">${order.id}</td>
                <td class="px-4 py-3 font-medium" style="color: var(--text)">${order.customerName}</td>
                <td class="px-4 py-3" style="color: var(--text-muted)" title="${itemSummary}">${truncate(itemSummary, 40)}</td>
                <td class="px-4 py-3 text-right font-semibold" style="color: var(--accent-dark)">Rp ${order.totalAmount.toLocaleString('id-ID')}</td>
                <td class="px-4 py-3">
                    <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-semibold" style="${badgeStyle}">${order.status}</span>
                </td>
            </tr>
        `;
    }).join('');
}

function truncate(text, max) {
    return text.length > max ? text.slice(0, max) + '…' : text;
}

function renderPagination(pagination) {
    currentPage = pagination.page;
    totalPages = pagination.totalPages;

    document.getElementById('pagination-info').innerText =
        `Halaman ${pagination.page} dari ${pagination.totalPages} (${pagination.totalRows} total pesanan)`;

    document.getElementById('btn-prev-page').disabled = currentPage <= 1;
    document.getElementById('btn-next-page').disabled = currentPage >= totalPages;
    document.getElementById('btn-prev-page').style.opacity = currentPage <= 1 ? 0.4 : 1;
    document.getElementById('btn-next-page').style.opacity = currentPage >= totalPages ? 0.4 : 1;
}

function changePage(delta) {
    const newPage = currentPage + delta;
    if (newPage < 1 || newPage > totalPages) return;
    currentPage = newPage;
    loadHistory();
}

document.getElementById('filter-form').addEventListener('submit', (e) => {
    e.preventDefault();
    currentPage = 1;
    loadHistory();
});

document.addEventListener('DOMContentLoaded', () => {
    loadStaffInfo();
    loadHistory();
});
