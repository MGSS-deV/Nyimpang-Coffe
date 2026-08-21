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

async function loadShiftStatus() {
    try {
        const response = await authFetch('/api/shifts_status.php');
        const data = await response.json();
        if (!data.success) return;

        const statusEl = document.getElementById('shift-status');
        const sinceEl = document.getElementById('shift-since');
        const btnIn = document.getElementById('btn-clockin');
        const btnOut = document.getElementById('btn-clockout');

        if (data.isClockedIn) {
            statusEl.innerText = '🟢 Sedang Bertugas';
            sinceEl.innerText = `Sejak ${new Date(data.clockInAt).toLocaleString('id-ID')}`;
            btnIn.classList.add('hidden');
            btnOut.classList.remove('hidden');
        } else {
            statusEl.innerText = '⚪ Belum Clock-In';
            sinceEl.innerText = '';
            btnIn.classList.remove('hidden');
            btnOut.classList.add('hidden');
        }
    } catch (error) { console.error('[SHIFT ERROR]', error); }
}

async function clockIn() {
    try {
        const response = await authFetch('/api/shifts_clockin.php', { method: 'POST' });
        const result = await response.json();
        if (result.success) loadShiftStatus();
        else alert(result.message);
    } catch (error) { console.error('[SHIFT ERROR]', error); }
}

async function clockOut() {
    try {
        const response = await authFetch('/api/shifts_clockout.php', { method: 'POST' });
        const result = await response.json();
        if (result.success) {
            loadShiftStatus();
            loadShiftHistory();
        } else alert(result.message);
    } catch (error) { console.error('[SHIFT ERROR]', error); }
}

async function loadShiftHistory() {
    const tbody = document.getElementById('shift-tbody');
    if (!tbody) return;
    try {
        const response = await authFetch('/api/shifts_list.php');
        const data = await response.json();
        if (!data.success) return;

        tbody.innerHTML = data.shifts.map(s => `
            <tr class="hairline-divider">
                <td class="px-4 py-3 font-medium" style="color: var(--text)">${s.staffUsername}</td>
                <td class="px-4 py-3" style="color: var(--text-muted)">${s.clockIn}</td>
                <td class="px-4 py-3" style="color: var(--text-muted)">${s.clockOut || '-'}</td>
                <td class="px-4 py-3 text-right" style="color: var(--text-muted)">${s.durationMin !== null ? s.durationMin + ' menit' : '-'}</td>
            </tr>
        `).join('');
    } catch (error) { console.error('[SHIFT ERROR]', error); }
}

document.addEventListener('DOMContentLoaded', () => {
    loadStaffInfo();
    loadShiftStatus();
    loadShiftHistory();
});
