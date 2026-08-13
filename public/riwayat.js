// ==========================================
// NYIMPANG COFFEE - RIWAYAT PESANAN + FILTER (FITUR BARU)
// ==========================================

let currentPage = 1;

// ---------- SESI LOGIN ----------
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

// ---------- FILTER & MUAT DATA ----------
function buildQuery() {
    const params = new URLSearchParams();
    const status = document.getElementById('filter-status').value;
    const dateFrom = document.getElementById('filter-date-from').value;
    const dateTo = document.getElementById('filter-date-to').value;
    const q = document.getElementById('filter-search').value;

    if (status) params.set('status', status);
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo) params.set('date_to', dateTo);
    if (q) params.set('q', q);
    params.set('page', currentPage);

    return params.toString();
}

async function loadHistory() {
    try {
        const response = await authFetch(`/api/orders_history.php?${buildQuery()}`);
        const data = await response.json();
        if (!data.success) return;

        renderTable(data.orders);
        renderPagination(data.pagination);
    } catch (error) {
        console.error('[RIWAYAT ERROR] Gagal memuat riwayat:', error);
    }
}

function renderTable(orders) {
    const container = document.getElementById('history-table-body');
    if (!container) return;

    if (orders.length === 0) {
        container.innerHTML = `<tr><td colspan="6" class="text-center py-10 text-xs" style="color: var(--text-faint)">Tidak ada pesanan yang cocok dengan filter ini.</td></tr>`;
        return;
    }

    container.innerHTML = orders.map(o => `
        <tr class="hairline-divider">
            <td class="py-3 px-3 text-xs" style="color: var(--text-faint)">${o.id}</td>
            <td class="py-3 px-3 text-xs font-medium" style="color: var(--text)">${o.customerName}</td>
            <td class="py-3 px-3 text-xs" style="color: var(--text-muted)">${o.orderType} • Meja ${o.tableNo}</td>
            <td class="py-3 px-3 text-xs" style="color: var(--text-muted)">${o.createdAtFull}</td>
            <td class="py-3 px-3 text-xs font-semibold" style="color: var(--accent-dark)">Rp ${o.totalAmount.toLocaleString('id-ID')}</td>
            <td class="py-3 px-3"><span class="badge-accent px-2 py-1 rounded-full text-[10px]">${o.status}</span></td>
        </tr>
    `).join('');
}

function renderPagination(pagination) {
    const info = document.getElementById('pagination-info');
    if (info) {
        info.innerText = pagination.total === 0
            ? 'Tidak ada data'
            : `Halaman ${pagination.page} dari ${pagination.totalPages} • ${pagination.total} pesanan ditemukan`;
    }

    const prevBtn = document.getElementById('btn-prev');
    const nextBtn = document.getElementById('btn-next');
    if (prevBtn) prevBtn.disabled = pagination.page <= 1;
    if (nextBtn) nextBtn.disabled = pagination.page >= pagination.totalPages;
}

// ---------- INISIALISASI ----------
document.addEventListener('DOMContentLoaded', () => {
    loadStaffInfo();
    loadHistory();

    document.getElementById('filter-form').addEventListener('submit', (e) => {
        e.preventDefault();
        currentPage = 1;
        loadHistory();
    });

    document.getElementById('btn-reset').addEventListener('click', () => {
        document.getElementById('filter-form').reset();
        currentPage = 1;
        loadHistory();
    });

    document.getElementById('btn-prev').addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            loadHistory();
        }
    });

    document.getElementById('btn-next').addEventListener('click', () => {
        currentPage++;
        loadHistory();
    });
});
