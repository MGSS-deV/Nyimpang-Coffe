let currentUserData = null;

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

async function loadIngredients() {
    try {
        const response = await authFetch('/api/ingredients_list.php');
        const data = await response.json();
        if (!data.success) return;
        renderIngredientList(data.ingredients);
    } catch (error) {
        console.error('[STOK ERROR]', error);
    }
}

function renderIngredientList(ingredients) {
    const container = document.getElementById('ingredient-list');
    if (ingredients.length === 0) {
        container.innerHTML = `<div class="text-center py-10 rounded-[var(--radius-md)]" style="border: 1px dashed var(--border)"><p class="text-xs" style="color: var(--text-faint)">Belum ada bahan baku. Tambahkan lewat form di atas.</p></div>`;
        return;
    }
    container.innerHTML = ingredients.map(i => `
        <div class="surface-card p-4 flex items-center justify-between gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h3 class="font-medium text-sm" style="color: var(--text)">${i.name}</h3>
                    ${i.isLow ? '<span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" style="background: #fbe9e7; color: var(--danger)">Stok Rendah</span>' : ''}
                </div>
                <p class="text-xs" style="color: var(--text-muted)">
                    Sisa: <strong style="color: var(--text)">${i.stockQty} ${i.unit}</strong>
                    · Batas rendah: ${i.lowStockThreshold} ${i.unit}
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button onclick='openRestockModal(${i.id}, ${JSON.stringify(i.name)})' class="btn-ghost text-xs px-3 py-2">+ Restock</button>
                <button onclick="deleteIngredient(${i.id})" class="btn-text-muted text-xs px-2 py-2">Hapus</button>
            </div>
        </div>
    `).join('');
}

document.getElementById('ingredient-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
        name: document.getElementById('ing-name').value,
        unit: document.getElementById('ing-unit').value,
        stockQty: Number(document.getElementById('ing-stock').value || 0),
        lowStockThreshold: Number(document.getElementById('ing-threshold').value || 0)
    };
    try {
        const response = await authFetch('/api/ingredients_create.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (result.success) {
            document.getElementById('ingredient-form').reset();
            loadIngredients();
        } else {
            alert('Gagal menambah: ' + result.message);
        }
    } catch (error) { console.error('[STOK ERROR]', error); }
});

async function deleteIngredient(id) {
    if (!confirm('Hapus bahan baku ini? Resep menu yang pakai bahan ini juga ikut terhapus.')) return;
    try {
        const response = await authFetch('/api/ingredients_delete.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id })
        });
        const result = await response.json();
        if (result.success) loadIngredients();
    } catch (error) { console.error('[STOK ERROR]', error); }
}

function openRestockModal(id, name) {
    document.getElementById('restock-ing-id').value = id;
    document.getElementById('restock-ing-name').innerText = name;
    document.getElementById('restock-qty').value = '';
    document.getElementById('restock-cost').value = '';
    document.getElementById('restock-modal').classList.remove('hidden');
}

function closeRestockModal() {
    document.getElementById('restock-modal').classList.add('hidden');
}

document.getElementById('restock-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
        id: Number(document.getElementById('restock-ing-id').value),
        addQty: Number(document.getElementById('restock-qty').value),
        cost: Number(document.getElementById('restock-cost').value || 0)
    };
    try {
        const response = await authFetch('/api/ingredients_restock.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (result.success) {
            closeRestockModal();
            loadIngredients();
        } else {
            alert('Gagal restock: ' + result.message);
        }
    } catch (error) { console.error('[STOK ERROR]', error); }
});

document.addEventListener('DOMContentLoaded', () => {
    loadStaffInfo();
    loadIngredients();
});
