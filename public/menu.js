// ==========================================
// NYIMPANG COFFEE - MANAJEMEN MENU / CRUD PRODUK (FITUR BARU)
// ==========================================

let currentProducts = [];
let editingId = null;

// ---------- SESI LOGIN ----------
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
    if (response.status === 403) {
        alert('Kamu tidak punya akses untuk aksi ini (khusus Admin).');
        throw new Error('Akses ditolak');
    }
    return response;
}

// ---------- MUAT DAFTAR MENU ----------
async function loadProducts() {
    try {
        const response = await authFetch('/api/products_list_admin.php');
        const data = await response.json();
        if (!data.success) return;
        currentProducts = data.products;
        renderTable(currentProducts);
    } catch (error) {
        console.error('[MENU ERROR] Gagal memuat menu:', error);
    }
}

function renderTable(products) {
    const container = document.getElementById('menu-table-body');
    if (!container) return;

    if (products.length === 0) {
        container.innerHTML = `<tr><td colspan="5" class="text-center py-10 text-xs" style="color: var(--text-faint)">Belum ada menu. Tambahkan menu pertama kamu.</td></tr>`;
        return;
    }

    container.innerHTML = products.map(p => `
        <tr class="hairline-divider ${p.isActive ? '' : 'opacity-50'}">
            <td class="py-3 px-3">
                <div class="flex items-center gap-2.5">
                    <span class="text-base">${p.icon || '☕'}</span>
                    <div>
                        <p class="text-xs font-medium" style="color: var(--text)">${p.name}</p>
                        <p class="text-[11px]" style="color: var(--text-faint)">${p.description || '-'}</p>
                    </div>
                </div>
            </td>
            <td class="py-3 px-3 text-xs" style="color: var(--text-muted)">${p.category}</td>
            <td class="py-3 px-3 text-xs font-semibold" style="color: var(--accent-dark)">Rp ${p.price.toLocaleString('id-ID')}</td>
            <td class="py-3 px-3">
                <span class="badge-accent px-2 py-1 rounded-full text-[10px]">${p.isActive ? 'Aktif' : 'Nonaktif'}</span>
            </td>
            <td class="py-3 px-3">
                <div class="flex gap-1.5 flex-wrap">
                    <button onclick="openEditModalById(${p.id})" class="btn-ghost px-2.5 py-1.5 text-[11px]">Edit</button>
                    <button onclick="toggleActive(${p.id})" class="btn-ghost px-2.5 py-1.5 text-[11px]">${p.isActive ? 'Nonaktifkan' : 'Aktifkan'}</button>
                    <button onclick="deleteProduct(${p.id})" class="btn-text-muted px-2.5 py-1.5 text-[11px]">Hapus</button>
                </div>
            </td>
        </tr>
    `).join('');
}

// ---------- MODAL TAMBAH/EDIT ----------
function openAddModal() {
    editingId = null;
    document.getElementById('modal-title').innerText = 'Tambah Menu';
    document.getElementById('product-form').reset();
    document.getElementById('product-active').checked = true;
    document.getElementById('product-modal').classList.remove('hidden');
}

function openEditModalById(id) {
    const p = currentProducts.find(x => x.id === id);
    if (!p) return;

    editingId = p.id;
    document.getElementById('modal-title').innerText = 'Edit Menu';
    document.getElementById('product-name').value = p.name;
    document.getElementById('product-description').value = p.description || '';
    document.getElementById('product-price').value = p.price;
    document.getElementById('product-category').value = p.category;
    document.getElementById('product-icon').value = p.icon || '☕';
    document.getElementById('product-active').checked = p.isActive;
    document.getElementById('product-modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('product-modal').classList.add('hidden');
}

// ---------- SIMPAN (TAMBAH / EDIT) ----------
document.addEventListener('DOMContentLoaded', () => {
    loadStaffInfo();
    loadProducts();

    document.getElementById('product-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const payload = {
            name: document.getElementById('product-name').value,
            description: document.getElementById('product-description').value,
            price: Number(document.getElementById('product-price').value),
            category: document.getElementById('product-category').value,
            icon: document.getElementById('product-icon').value,
            isActive: document.getElementById('product-active').checked
        };

        const url = editingId ? '/api/products_update.php' : '/api/products_create.php';
        if (editingId) payload.id = editingId;

        try {
            const response = await authFetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            if (result.success) {
                closeModal();
                loadProducts();
            } else {
                alert('Gagal menyimpan menu: ' + result.message);
            }
        } catch (error) {
            console.error('[MENU ERROR]', error);
        }
    });
});

// ---------- AKTIFKAN / NONAKTIFKAN ----------
async function toggleActive(id) {
    const p = currentProducts.find(x => x.id === id);
    if (!p) return;

    try {
        const response = await authFetch('/api/products_update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: p.id,
                name: p.name,
                description: p.description,
                price: p.price,
                category: p.category,
                icon: p.icon,
                isActive: !p.isActive
            })
        });
        const result = await response.json();
        if (result.success) {
            loadProducts();
        } else {
            alert('Gagal memperbarui status: ' + result.message);
        }
    } catch (error) {
        console.error('[MENU ERROR]', error);
    }
}

// ---------- HAPUS PERMANEN ----------
async function deleteProduct(id) {
    if (!confirm('Hapus menu ini secara permanen? Menu yang pernah dipesan tetap muncul apa adanya di riwayat pesanan lama.')) return;

    try {
        const response = await authFetch('/api/products_delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await response.json();
        if (result.success) {
            loadProducts();
        } else {
            alert('Gagal menghapus menu: ' + result.message);
        }
    } catch (error) {
        console.error('[MENU ERROR]', error);
    }
}
