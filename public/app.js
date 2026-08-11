// ==========================================
// NYIMPANG COFFEE - CLIENT PELANGGAN (SELF ORDER, VERSI PHP/POLLING)
// ==========================================

let cart = [];
let pendingOrderData = null;
let activeOrderId = null;
let statusPollTimer = null;
const DANA_NUMBER = "081234567890"; // Ganti nomor DANA kamu di sini
const POLL_INTERVAL_MS = 2500; // Realtime "semu" - cek status tiap 2.5 detik

// ---------- MENU (diambil dari server, bukan hardcode) ----------
async function loadMenu() {
    const container = document.getElementById('menu-container');
    if (!container) return;

    try {
        const response = await fetch('/api/products_list.php');
        const data = await response.json();

        if (!data.success || !data.products || data.products.length === 0) {
            container.innerHTML = `<p class="text-xs col-span-full" style="color: var(--text-faint)">Belum ada menu tersedia.</p>`;
            return;
        }

        container.innerHTML = data.products.map(p => `
            <div class="surface-card p-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-[var(--radius-sm)] flex items-center justify-center text-xl shrink-0" style="background: var(--border-soft)">${p.icon || '☕'}</div>
                    <div>
                        <h3 class="font-medium text-sm" style="color: var(--text)">${p.name}</h3>
                        <p class="text-xs text-[var(--text-muted)] mt-0.5">${p.description || ''}</p>
                        <span class="text-sm font-semibold" style="color: var(--accent-dark)">Rp ${p.price.toLocaleString('id-ID')}</span>
                    </div>
                </div>
                <button data-name="${p.name}" data-price="${p.price}" class="btn-add-menu btn-ghost text-xs px-3 py-2 shrink-0">+ Tambah</button>
            </div>
        `).join('');
    } catch (error) {
        console.error('Gagal memuat menu:', error);
        container.innerHTML = `<p class="text-xs col-span-full" style="color: var(--danger)">Gagal memuat menu. Coba refresh halaman.</p>`;
    }
}

document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-add-menu');
    if (!btn) return;
    addToCart(btn.dataset.name, Number(btn.dataset.price));
});

document.addEventListener('DOMContentLoaded', loadMenu);

// ---------- KERANJANG ----------
function addToCart(name, price) {
    const existingItem = cart.find(item => item.name === name);
    if (existingItem) {
        existingItem.qty += 1;
    } else {
        cart.push({ id: 'ITM-' + Date.now(), name, price, qty: 1 });
    }
    updateCartUI();
}

function updateCartUI() {
    const container = document.getElementById('cart-items-container');
    const badge = document.getElementById('cart-badge');
    const totalPriceEl = document.getElementById('cart-total-price');

    const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
    const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);

    if (badge) badge.innerText = totalQty;
    if (totalPriceEl) totalPriceEl.innerText = `Rp ${totalPrice.toLocaleString('id-ID')}`;

    if (!container) return;

    if (cart.length === 0) {
        container.innerHTML = `<p class="text-center text-xs py-8" style="color: var(--text-faint)">Keranjang kamu masih kosong.</p>`;
        return;
    }

    container.innerHTML = cart.map((item, index) => `
        <div class="flex justify-between items-center px-3 py-2.5 rounded-[var(--radius-sm)]" style="background: var(--bg); border: 1px solid var(--border-soft)">
            <div>
                <h4 class="text-xs font-medium" style="color: var(--text)">${item.name}</h4>
                <p class="text-[11px] text-[var(--text-muted)]">Rp ${item.price.toLocaleString('id-ID')} x ${item.qty}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold" style="color: var(--accent-dark)">Rp ${(item.price * item.qty).toLocaleString('id-ID')}</span>
                <button onclick="removeFromCart(${index})" class="text-[var(--text-faint)] hover:text-[var(--danger)] text-xs cursor-pointer">&times;</button>
            </div>
        </div>
    `).join('');
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartUI();
}

function toggleCartModal(open) {
    const modal = document.getElementById('cart-modal');
    if (!modal) return;
    if (open) modal.classList.remove('hidden');
    else modal.classList.add('hidden');
}

// ---------- CHECKOUT ----------
document.addEventListener('DOMContentLoaded', () => {
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const customerName = document.getElementById('customer-name').value;
            const orderType = document.getElementById('order-type').value;
            const tableNo = document.getElementById('table-number').value;
            const paymentMethod = document.getElementById('payment-method').value;

            if (cart.length === 0) {
                alert('Keranjang kamu masih kosong!');
                return;
            }

            const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);

            pendingOrderData = {
                customerName,
                orderType,
                tableNo,
                paymentMethod,
                items: cart,
                totalAmount: totalPrice
            };

            if (paymentMethod === 'Kasir / Cash') {
                submitOrderToServer(pendingOrderData);
            } else {
                showPaymentModal(paymentMethod, totalPrice);
            }
        });
    }
});

function showPaymentModal(method, total) {
    const paymentModal = document.getElementById('payment-modal');
    const payTitle = document.getElementById('pay-title');
    const payContent = document.getElementById('pay-content');

    paymentModal.classList.remove('hidden');
    toggleCartModal(false);

    document.getElementById('pay-total').innerText = `Rp ${total.toLocaleString('id-ID')}`;
    payTitle.innerText = `Pembayaran via ${method}`;

    if (method === 'QRIS') {
        payContent.innerHTML = `
            <p class="text-xs" style="color: var(--text-muted)">Scan QRIS menggunakan M-Banking / E-Wallet</p>
            <div class="bg-white p-3 rounded-[var(--radius-sm)] border border-[var(--border)]">
                <img id="qris-img" src="assets/qris-sample.png" alt="QRIS" class="w-40 h-40 object-contain mx-auto" onerror="this.src='https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=NyimpangCoffeeQRIS';">
            </div>
            <a id="download-qris-btn" href="#" onclick="downloadQRIS(event)" class="btn-ghost inline-block px-4 py-2 text-xs">
                Simpan QR Code
            </a>
        `;
    } else if (method === 'DANA') {
        const danaDeepLink = `https://link.dana.id/sendmoney?phone=${DANA_NUMBER}&amount=${total}`;
        payContent.innerHTML = `
            <p class="text-xs" style="color: var(--text-muted)">Transfer ke Nomor DANA</p>
            <div class="text-sm font-semibold px-4 py-2 rounded-[var(--radius-sm)] bg-white border border-[var(--border)] select-all" style="color: var(--accent-dark)">${DANA_NUMBER}</div>
            <a href="${danaDeepLink}" target="_blank" class="btn-primary w-full py-2.5 px-4 text-xs inline-block">
                Bayar Otomatis via Aplikasi DANA
            </a>
        `;
    }
}

function downloadQRIS(e) {
    e.preventDefault();
    const imgElement = document.getElementById('qris-img');
    const link = document.createElement('a');
    link.href = imgElement.src;
    link.download = 'QRIS-Nyimpang-Coffee.png';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Dipanggil saat pelanggan menekan "Saya Sudah Bayar" -> titik pesanan
// dianggap SUKSES BAYAR, langsung dikirim ke server & disiarkan ke Barista.
async function confirmPayment() {
    if (!pendingOrderData) return;
    await submitOrderToServer(pendingOrderData);
    document.getElementById('payment-modal').classList.add('hidden');
}

async function submitOrderToServer(payload) {
    try {
        const response = await fetch('/api/orders_create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data.success) {
            activeOrderId = data.order.id;
            startOrderTracking(data.order);

            cart = [];
            updateCartUI();
            toggleCartModal(false);
        } else {
            alert('Gagal membuat pesanan: ' + data.message);
        }
    } catch (error) {
        console.error('Error Checkout:', error);
        alert('Terjadi kesalahan koneksi ke server.');
    }
}

// ---------- PELACAKAN STATUS (polling tiap 2.5 detik, bukan realtime instan) ----------
function startOrderTracking(order) {
    const tracker = document.getElementById('order-tracker');
    if (!tracker) return;
    tracker.classList.remove('hidden');
    document.getElementById('tracker-order-id').innerText = `${order.id} • ${order.customerName}`;
    updateTrackerUI(order.status);
    tracker.scrollIntoView({ behavior: 'smooth', block: 'start' });

    if (statusPollTimer) clearInterval(statusPollTimer);
    statusPollTimer = setInterval(pollOrderStatus, POLL_INTERVAL_MS);
}

async function pollOrderStatus() {
    if (!activeOrderId) return;
    try {
        const response = await fetch(`/api/order_status.php?id=${encodeURIComponent(activeOrderId)}`);
        const data = await response.json();
        if (!data.success) return;

        updateTrackerUI(data.order.status);

        // Pesanan sudah kelar -> nggak perlu polling terus-terusan
        if (data.order.status === 'Selesai' || data.order.status === 'Dibatalkan') {
            clearInterval(statusPollTimer);
        }
    } catch (error) {
        console.error('Gagal polling status pesanan:', error);
    }
}

let lastKnownStatus = null;

function updateTrackerUI(status) {
    const badge = document.getElementById('tracker-status-badge');
    if (badge) badge.innerText = status;

    const steps = { 'Masuk': 1, 'Dibuat': 2, 'Siap Diambil': 3, 'Selesai': 3 };
    const activeStep = steps[status] || 1;
    const stepIds = ['step-masuk', 'step-dibuat', 'step-siap'];

    stepIds.forEach((id, i) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.className = (i + 1 <= activeStep)
            ? 'w-2.5 h-2.5 mx-auto rounded-full dot-accent-full'
            : 'w-2.5 h-2.5 mx-auto rounded-full bg-[var(--border)]';
    });

    // Bunyi cuma sekali pas transisi ke "Siap Diambil", bukan tiap polling
    if (status === 'Siap Diambil' && lastKnownStatus !== 'Siap Diambil') {
        playPickupSound();
    }
    lastKnownStatus = status;
}

function playPickupSound() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, ctx.currentTime);
        gain.gain.setValueAtTime(0.2, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.5);
    } catch (e) {
        console.warn('Audio belum diaktifkan browser.');
    }
}
