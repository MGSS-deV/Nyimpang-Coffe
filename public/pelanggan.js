async function loadStaffInfo() {
    try {
        const response = await fetch('/api/auth_me.php');
        const data = await response.json();
        const badge = document.getElementById('staff-badge');
        if (data.success && badge) badge.innerText = `👤 ${data.user.username} (${data.user.role})`;
    } catch (error) { console.error('[AUTH] Gagal memuat info staff:', error); }
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

async function loadCustomers() {
    try {
        const response = await authFetch('/api/customers_list.php');
        const data = await response.json();
        if (!data.success) return;

        const tbody = document.getElementById('customer-tbody');
        const emptyEl = document.getElementById('customer-empty');

        if (data.customers.length === 0) {
            tbody.innerHTML = '';
            emptyEl.classList.remove('hidden');
            return;
        }
        emptyEl.classList.add('hidden');

        tbody.innerHTML = data.customers.map((c, idx) => `
            <tr class="hairline-divider">
                <td class="px-4 py-3 font-medium" style="color: var(--text)">${idx === 0 ? '⭐ ' : ''}${c.name}</td>
                <td class="px-4 py-3" style="color: var(--text-muted)">${c.phone}</td>
                <td class="px-4 py-3 text-right" style="color: var(--text)">${c.orderCount}x</td>
                <td class="px-4 py-3 text-right font-semibold" style="color: var(--accent-dark)">Rp ${c.totalSpent.toLocaleString('id-ID')}</td>
                <td class="px-4 py-3" style="color: var(--text-muted)">${c.lastOrderAt}</td>
            </tr>
        `).join('');
    } catch (error) { console.error('[PELANGGAN ERROR]', error); }
}

document.addEventListener('DOMContentLoaded', () => {
    loadStaffInfo();
    loadCustomers();
});
