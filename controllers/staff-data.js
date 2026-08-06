// ==========================================
// DAFTAR AKUN STAFF
// ==========================================
// Tinggal tambah/ubah/hapus baris di bawah untuk kelola siapa aja
// yang boleh login ke Dashboard Barista & Laporan Keuangan.
//
// ⚠️ GANTI password default ini sebelum dipakai beneran!

const bcrypt = require('bcryptjs');

const rawStaff = [
    { username: 'barista', password: 'barista123', role: 'Barista' },
    { username: 'admin', password: 'admin123', role: 'Admin' }
];

// Password di-hash sekali saat server nyala, jadi tidak disimpan plain text di memori
const staff = rawStaff.map(s => ({
    username: s.username,
    role: s.role,
    passwordHash: bcrypt.hashSync(s.password, 8)
}));

module.exports = staff;
