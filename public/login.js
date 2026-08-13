document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const username = document.getElementById('login-username').value;
    const password = document.getElementById('login-password').value;
    const errorEl = document.getElementById('login-error');
    errorEl.classList.add('hidden');

    try {
        const response = await fetch('/api/auth_login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        const data = await response.json();

        if (data.success) {
            const params = new URLSearchParams(window.location.search);
            // FITUR BARU: setelah login, arahkan ke Dashboard Ringkasan/Analitik
            // (bukan langsung ke papan barista), jadi staff langsung lihat
            // gambaran umum toko begitu masuk.
            const redirectTo = params.get('redirect') || 'dashboard.php';
            window.location.href = redirectTo;
        } else {
            errorEl.innerText = data.message || 'Login gagal';
            errorEl.classList.remove('hidden');
        }
    } catch (error) {
        console.error('[LOGIN ERROR]', error);
        errorEl.innerText = 'Terjadi kesalahan koneksi.';
        errorEl.classList.remove('hidden');
    }
});
