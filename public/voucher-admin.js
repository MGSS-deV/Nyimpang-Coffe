async function loadStaffInfo() {
    try {
        const response = await fetch('/api/auth_me.php');
        const data = await response.json();
        const badge = document.getElementById('staff-badge');
        if (data.success && badge) badge.innerText = `👤 ${data.user.username} (${data.user.role})`;
    } catch (error) { console.error(error); }
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

async function loadVouchers() {
    try {
        const response = await authFetch('/api/vouchers_list.php');
        const data = await response.json();
        if (!data.success) return;
        renderVoucherList(data.vouchers);
    } catch (error) { console.error('[VOUCHER ERROR]', error); }
}

function renderVoucherList(vouchers) {
    const container = document.getElementById('voucher-list');
    if (vouchers.length === 0) {
        container.innerHTML = `<div class="text-center py-10 rounded-[var(--radius-md)]" style="border: 1px dashed var(--border)"><p class="text-xs" style="color: var(--text-faint)">Belum ada voucher.</p></div>`;
        return;
    }

    container.innerHTML = vouchers.map(v => {
        const valueLabel = v.discountType === 'percent' ? `${v.discountValue}%` : `Rp ${v.discountValue.toLocaleString('id-ID')}`;
        const usageLabel = v.maxUses ? `${v.usedCount}/${v.maxUses}x dipakai` : `${v.usedCount}x dipakai (tanpa batas)`;
        const expiredLabel = v.expiresAt ? `Berlaku sampai ${new Date(v.expiresAt).toLocaleDateString('id-ID')}` : 'Tanpa batas waktu';

        return `
            <div class="surface-card p-4 flex items-center justify-between gap-4 ${v.isActive ? '' : 'opacity-50'}">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="font-display text-base" style="color: var(--accent-dark)">${v.code}</h3>
                        <span class="badge-accent text-[10px] px-2 py-0.5 rounded-full">${valueLabel}</span>
                        ${!v.isActive ? '<span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" style="background: var(--border-soft); color: var(--text-muted)">Nonaktif</span>' : ''}
                    </div>
                    <p class="text-xs mt-1" style="color: var(--text-muted)">
                        Min. belanja Rp ${v.minPurchase.toLocaleString('id-ID')} · ${usageLabel} · ${expiredLabel}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button onclick="toggleVoucher(${v.id}, ${!v.isActive})" class="btn-ghost text-xs px-3 py-2">${v.isActive ? 'Nonaktifkan' : 'Aktifkan'}</button>
                    <button onclick="deleteVoucher(${v.id})" class="btn-text-muted text-xs px-2 py-2">Hapus</button>
                </div>
            </div>
        `;
    }).join('');
}

document.getElementById('voucher-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
        code: document.getElementById('v-code').value,
        discountType: document.getElementById('v-type').value,
        discountValue: Number(document.getElementById('v-value').value),
        minPurchase: Number(document.getElementById('v-min').value || 0),
        maxUses: document.getElementById('v-max').value || null,
        expiresAt: document.getElementById('v-expires').value || null
    };

    try {
        const response = await authFetch('/api/vouchers_create.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (result.success) {
            document.getElementById('voucher-form').reset();
            loadVouchers();
        } else {
            alert('Gagal membuat voucher: ' + result.message);
        }
    } catch (error) { console.error('[VOUCHER ERROR]', error); }
});

async function toggleVoucher(id, newActiveState) {
    try {
        const response = await authFetch('/api/vouchers_update.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id, isActive: newActiveState })
        });
        const result = await response.json();
        if (result.success) loadVouchers();
    } catch (error) { console.error('[VOUCHER ERROR]', error); }
}

async function deleteVoucher(id) {
    if (!confirm('Hapus voucher ini?')) return;
    try {
        const response = await authFetch('/api/vouchers_delete.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id })
        });
        const result = await response.json();
        if (result.success) loadVouchers();
    } catch (error) { console.error('[VOUCHER ERROR]', error); }
}

document.addEventListener('DOMContentLoaded', () => {
    loadStaffInfo();
    loadVouchers();
});
