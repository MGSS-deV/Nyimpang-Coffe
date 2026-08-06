// Menjaga route halaman & API yang cuma boleh diakses staff yang sudah login.
module.exports = function requireAuth(req, res, next) {
    if (req.session && req.session.user) {
        return next();
    }

    // Request API -> balas JSON 401 (dipakai fetch di bar.js / keuangan.js)
    // Pakai originalUrl (bukan req.path) karena path relatif ke mount point saat dipasang lewat router.
    if (req.originalUrl.startsWith('/api/')) {
        return res.status(401).json({ success: false, message: 'Sesi habis, silakan login lagi' });
    }

    // Request halaman langsung dari browser -> lempar ke halaman login,
    // sambil bawa tujuan awal supaya balik ke sana setelah berhasil login.
    return res.redirect(`/login.html?redirect=${encodeURIComponent(req.originalUrl)}`);
};
