// ==========================================
// NYIMPANG COFFEE - LAPORAN KEUANGAN
// ==========================================

const socket = io();

// ---------- SESI LOGIN ----------
async function loadStaffInfo() {
    try {
        const response = await fetch('/api/auth/me');
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
    await fetch('/api/auth/logout', { method: 'POST' });
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

async function loadSummary() {
    try {
        const response = await authFetch('/api/finance/summary');
        const data = await response.json();
        if (!data.success) return;

        document.getElementById('stat-income').innerText = `Rp ${data.totalIncome.toLocaleString('id-ID')}`;
        document.getElementById('stat-expense').innerText = `Rp ${data.totalExpense.toLocaleString('id-ID')}`;
        document.getElementById('stat-net').innerText = `Rp ${data.netProfit.toLocaleString('id-ID')}`;

        renderIncomeList(data.incomeEntries);
        renderExpenseList(data.expenseEntries);
    } catch (error) {
        console.error('[KEUANGAN ERROR] Gagal memuat ringkasan:', error);
    }
}

function renderIncomeList(entries) {
    const container = document.getElementById('income-list');
    if (!container) return;

    if (entries.length === 0) {
        container.innerHTML = emptyState('Belum ada pemasukan. Pemasukan otomatis muncul saat pesanan berstatus Selesai.');
        return;
    }

    container.innerHTML = entries.map(e => `
        <div class="surface-card p-3.5 flex justify-between items-center gap-3">
            <div class="min-w-0">
                <p class="text-xs font-medium truncate" style="color: var(--text)">${e.description}</p>
                <p class="text-[11px]" style="color: var(--text-faint)">${e.createdAt}</p>
            </div>
            <span class="text-xs font-semibold shrink-0" style="color: var(--accent-dark)">+ Rp ${e.amount.toLocaleString('id-ID')}</span>
        </div>
    `).join('');
}

function renderExpenseList(entries) {
    const container = document.getElementById('expense-list');
    if (!container) return;

    if (entries.length === 0) {
        container.innerHTML = emptyState('Belum ada pengeluaran tercatat.');
        return;
    }

    container.innerHTML = entries.map(e => `
        <div class="surface-card p-3.5 flex justify-between items-center gap-3">
            <div class="min-w-0">
                <p class="text-xs font-medium truncate" style="color: var(--text)">${e.description}</p>
                <p class="text-[11px]" style="color: var(--text-faint)">${e.category} • ${e.createdAt}</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <span class="text-xs font-semibold" style="color: var(--danger)">- Rp ${e.amount.toLocaleString('id-ID')}</span>
                <button onclick="removeExpense('${e.id}')" class="text-[var(--text-faint)] hover:text-[var(--danger)] text-xs cursor-pointer">&times;</button>
            </div>
        </div>
    `).join('');
}

function emptyState(message) {
    return `
        <div class="text-center py-8 rounded-[var(--radius-md)]" style="border: 1px dashed var(--border)">
            <p class="text-xs" style="color: var(--text-faint)">${message}</p>
        </div>
    `;
}

document.getElementById('expense-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const description = document.getElementById('expense-description').value;
    const amount = document.getElementById('expense-amount').value;
    const category = document.getElementById('expense-category').value;

    try {
        const response = await authFetch('/api/finance/expenses', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ description, amount, category })
        });
        const result = await response.json();

        if (result.success) {
            document.getElementById('expense-form').reset();
            loadSummary();
        } else {
            alert('Gagal mencatat pengeluaran: ' + result.message);
        }
    } catch (error) {
        console.error('[KEUANGAN ERROR]', error);
        alert('Terjadi kesalahan koneksi.');
    }
});

async function removeExpense(id) {
    if (!confirm('Hapus catatan pengeluaran ini?')) return;
    try {
        const response = await authFetch(`/api/finance/expenses/${id}`, { method: 'DELETE' });
        const result = await response.json();
        if (result.success) loadSummary();
    } catch (error) {
        console.error('[KEUANGAN ERROR]', error);
    }
}

// Realtime: refresh tiap ada pesanan baru, status berubah, atau pengeluaran diubah dari tab lain
socket.on('pesanan-baru', loadSummary);
socket.on('status-diperbarui', loadSummary);
socket.on('keuangan-diperbarui', loadSummary);

document.addEventListener('DOMContentLoaded', () => {
    loadStaffInfo();
    loadSummary();
});
