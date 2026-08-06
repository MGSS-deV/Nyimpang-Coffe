const bcrypt = require('bcryptjs');
const staff = require('./staff-data');

// POST /api/auth/login
exports.login = (req, res) => {
    const { username, password } = req.body;

    const user = staff.find(s => s.username === username);
    const passwordValid = user && bcrypt.compareSync(password || '', user.passwordHash);

    if (!passwordValid) {
        return res.status(401).json({ success: false, message: 'Username atau password salah' });
    }

    req.session.user = { username: user.username, role: user.role };
    res.json({ success: true, message: 'Login berhasil', user: req.session.user });
};

// POST /api/auth/logout
exports.logout = (req, res) => {
    req.session.destroy(() => {
        res.clearCookie('connect.sid');
        res.json({ success: true, message: 'Logout berhasil' });
    });
};

// GET /api/auth/me -> dipakai halaman untuk tahu siapa yang login
exports.me = (req, res) => {
    if (req.session && req.session.user) {
        return res.json({ success: true, user: req.session.user });
    }
    res.status(401).json({ success: false, message: 'Belum login' });
};
