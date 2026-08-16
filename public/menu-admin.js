// ==========================================
// NYIMPANG COFFEE - MANAJEMEN MENU (CRUD)
// ==========================================

let editingId = null;

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

async function loadProducts() {
    try {
        const response = await authFetch('/api/products_admin_list.php');
        const data = await response.json();
        if (!data.success) return;
        renderProductList(data.products);
    } catch (error) {
        console.error('[MENU ERROR]', error);
    }
}

function renderProductList(products) {
    const container = document.getElementById('product-list');

    if (products.length === 0) {
        container.innerHTML = `
            <div class="text-center py-10 rounded-[var(--radius-md)]" style="border: 1px dashed var(--border)">
                <p class="text-xs" style="color: var(--text-faint)">Belum ada menu. Tambahkan lewat form di atas.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = products.map(p => `
        <div class="surface-card p-4 flex items-center justify-between gap-4 ${p.isActive ? '' : 'opacity-50'}">
            <div class="flex items-center gap-4 min-w-0">
                <div class="w-12 h-12 rounded-[var(--radius-sm)] flex items-center justify-center text-xl shrink-0" style="background: var(--border-soft)">${p.icon}</div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="font-medium text-sm truncate" style="color: var(--text)">${p.name}</h3>
                        ${!p.isActive ? '<span class="text-[10px] font-semibold px-2 py-0.5 rounded-full shrink-0" style="background: var(--border-soft); color: var(--text-muted)">Nonaktif</span>' : ''}
                    </div>
                    <p class="text-xs truncate" style="color: var(--text-muted)">${p.description || '-'} • ${p.category}</p>
                    <span class="text-xs font-semibold" style="color: var(--accent-dark)">Rp ${p.price.toLocaleString('id-ID')}</span>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button onclick='startEdit(${JSON.stringify(p)})' class="btn-ghost text-xs px-3 py-2">Edit</button>
                <button onclick='openRecipeModal(${p.id}, ${JSON.stringify(p.name)})' class="btn-ghost text-xs px-3 py-2">Resep</button>
                <button onclick="toggleActive(${p.id}, ${!p.isActive})" class="btn-ghost text-xs px-3 py-2">${p.isActive ? 'Nonaktifkan' : 'Aktifkan'}</button>
                <button onclick="deleteProduct(${p.id})" class="btn-text-muted text-xs px-2 py-2">Hapus</button>
            </div>
        </div>
    `).join('');
}

function startEdit(product) {
    editingId = product.id;
    document.getElementById('product-id').value = product.id;
    document.getElementById('product-name').value = product.name;
    document.getElementById('product-description').value = product.description || '';
    document.getElementById('product-price').value = product.price;
    document.getElementById('product-icon').value = product.icon;
    document.getElementById('product-category').value = product.category;

    document.getElementById('form-title').innerText = `Edit: ${product.name}`;
    document.getElementById('submit-btn').innerText = 'Update Menu';
    document.getElementById('cancel-edit-btn').classList.remove('hidden');

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function cancelEdit() {
    editingId = null;
    document.getElementById('product-form').reset();
    document.getElementById('product-id').value = '';
    document.getElementById('form-title').innerText = 'Tambah Menu Baru';
    document.getElementById('submit-btn').innerText = 'Simpan Menu';
    document.getElementById('cancel-edit-btn').classList.add('hidden');
}

document.getElementById('product-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const payload = {
        name: document.getElementById('product-name').value,
        description: document.getElementById('product-description').value,
        price: Number(document.getElementById('product-price').value),
        icon: document.getElementById('product-icon').value,
        category: document.getElementById('product-category').value
    };

    try {
        let response;
        if (editingId) {
            response = await authFetch('/api/products_update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: editingId, ...payload })
            });
        } else {
            response = await authFetch('/api/products_create.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
        }

        const result = await response.json();
        if (result.success) {
            cancelEdit();
            loadProducts();
        } else {
            alert('Gagal menyimpan: ' + result.message);
        }
    } catch (error) {
        console.error('[MENU ERROR]', error);
        alert('Terjadi kesalahan koneksi.');
    }
});

async function toggleActive(id, newActiveState) {
    try {
        const response = await authFetch('/api/products_update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, isActive: newActiveState })
        });
        const result = await response.json();
        if (result.success) loadProducts();
    } catch (error) {
        console.error('[MENU ERROR]', error);
    }
}

async function deleteProduct(id) {
    if (!confirm('Hapus menu ini secara permanen? Riwayat pesanan lama nggak akan terpengaruh.')) return;
    try {
        const response = await authFetch('/api/products_delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await response.json();
        if (result.success) loadProducts();
    } catch (error) {
        console.error('[MENU ERROR]', error);
    }
}

// ---------- MODAL ATUR RESEP ----------
let recipeProductId = null;

async function openRecipeModal(productId, productName) {
    recipeProductId = productId;
    document.getElementById('recipe-product-name').innerText = productName;
    document.getElementById('recipe-modal').classList.remove('hidden');
    document.getElementById('recipe-ingredient-list').innerHTML = `<p class="text-xs" style="color: var(--text-faint)">Memuat...</p>`;

    try {
        const [ingredientsRes, recipeRes] = await Promise.all([
            authFetch('/api/ingredients_list.php'),
            authFetch(`/api/product_ingredients_get.php?product_id=${productId}`)
        ]);
        const ingredientsData = await ingredientsRes.json();
        const recipeData = await recipeRes.json();

        const currentRecipe = {};
        (recipeData.recipe || []).forEach(r => { currentRecipe[r.ingredientId] = r.qtyPerServing; });

        const ingredients = ingredientsData.ingredients || [];
        if (ingredients.length === 0) {
            document.getElementById('recipe-ingredient-list').innerHTML =
                `<p class="text-xs" style="color: var(--text-faint)">Belum ada bahan baku. Tambahkan dulu di halaman Stok.</p>`;
            return;
        }

        document.getElementById('recipe-ingredient-list').innerHTML = ingredients.map(ing => {
            const isChecked = currentRecipe[ing.id] !== undefined;
            const qtyValue = isChecked ? currentRecipe[ing.id] : '';
            return `
                <div class="flex items-center gap-3 p-2.5 rounded-[var(--radius-sm)]" style="background: var(--bg)">
                    <input type="checkbox" id="recipe-check-${ing.id}" ${isChecked ? 'checked' : ''} class="recipe-checkbox" data-ingredient-id="${ing.id}">
                    <label for="recipe-check-${ing.id}" class="text-xs flex-1" style="color: var(--text)">${ing.name} <span style="color: var(--text-faint)">(${ing.unit})</span></label>
                    <input type="number" step="0.01" min="0.01" placeholder="jumlah" value="${qtyValue}"
                        id="recipe-qty-${ing.id}" class="w-20 bg-white border border-[var(--border)] rounded-[var(--radius-sm)] px-2 py-1 text-xs">
                </div>
            `;
        }).join('');
    } catch (error) {
        console.error('[RESEP ERROR]', error);
    }
}

function closeRecipeModal() {
    document.getElementById('recipe-modal').classList.add('hidden');
    recipeProductId = null;
}

async function saveRecipe() {
    const checkboxes = document.querySelectorAll('.recipe-checkbox');
    const recipe = [];

    checkboxes.forEach(cb => {
        if (!cb.checked) return;
        const ingredientId = Number(cb.dataset.ingredientId);
        const qtyInput = document.getElementById(`recipe-qty-${ingredientId}`);
        const qty = Number(qtyInput.value);
        if (qty > 0) recipe.push({ ingredientId, qtyPerServing: qty });
    });

    try {
        const response = await authFetch('/api/product_ingredients_set.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ productId: recipeProductId, recipe })
        });
        const result = await response.json();
        if (result.success) {
            closeRecipeModal();
        } else {
            alert('Gagal menyimpan resep: ' + result.message);
        }
    } catch (error) {
        console.error('[RESEP ERROR]', error);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadStaffInfo();
    loadProducts();
});
