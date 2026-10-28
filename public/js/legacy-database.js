/**
 * SIPADES - Database Layer
 * Simulasi database menggunakan localStorage
 * Struktur tabel sesuai ERD BAB VI
 */

class Database {
    constructor() {
        this.prefix = 'sipades_';
        this.tables = [
            'users', 'masyarakat', 'petugas', 'kepala_desa_lurah',
            'jenis_pelayanan', 'pengajuan', 'dokumen', 'verifikasi',
            'surat', 'notifikasi'
        ];
        if (!localStorage.getItem(this.prefix + 'initialized')) {
            this.seed();
        }
    }

    // ==================== CORE CRUD ====================

    _key(table) { return this.prefix + table; }

    getAll(table) {
        const data = localStorage.getItem(this._key(table));
        return data ? JSON.parse(data) : [];
    }

    getById(table, idField, id) {
        return this.getAll(table).find(r => r[idField] === id) || null;
    }

    findWhere(table, conditions) {
        return this.getAll(table).filter(row => {
            return Object.keys(conditions).every(key => row[key] === conditions[key]);
        });
    }

    insert(table, record) {
        const data = this.getAll(table);
        data.push(record);
        localStorage.setItem(this._key(table), JSON.stringify(data));
        return record;
    }

    update(table, idField, id, updates) {
        const data = this.getAll(table);
        const idx = data.findIndex(r => r[idField] === id);
        if (idx === -1) return null;
        data[idx] = { ...data[idx], ...updates };
        localStorage.setItem(this._key(table), JSON.stringify(data));
        return data[idx];
    }

    remove(table, idField, id) {
        const data = this.getAll(table).filter(r => r[idField] !== id);
        localStorage.setItem(this._key(table), JSON.stringify(data));
    }

    generateId() {
        return 'id_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    count(table, conditions = null) {
        if (!conditions) return this.getAll(table).length;
        return this.findWhere(table, conditions).length;
    }

    // ==================== USER OPERATIONS ====================

    findUserByEmail(email) {
        return this.getAll('users').find(u => u.email === email) || null;
    }

    createUser(nama, email, password, role) {
        const id_user = this.generateId();
        const user = { id_user, nama, email, password: this.hashPassword(password), role, created_at: new Date().toISOString() };
        this.insert('users', user);
        return user;
    }

    registerMasyarakat(nama, email, password, nik, alamat, no_hp) {
        const user = this.createUser(nama, email, password, 'masyarakat');
        const masyarakat = {
            id_masyarakat: this.generateId(),
            id_user: user.id_user,
            nik, alamat, no_hp
        };
        this.insert('masyarakat', masyarakat);
        return { user, masyarakat };
    }

    getMasyarakatByUserId(id_user) {
        return this.findWhere('masyarakat', { id_user })[0] || null;
    }

    getPetugasByUserId(id_user) {
        return this.findWhere('petugas', { id_user })[0] || null;
    }

    getKepalaByUserId(id_user) {
        return this.findWhere('kepala_desa_lurah', { id_user })[0] || null;
    }

    hashPassword(pwd) {
        // Simple hash for demo — NOT secure for production
        let hash = 0;
        for (let i = 0; i < pwd.length; i++) {
            const char = pwd.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash |= 0;
        }
        return 'h_' + Math.abs(hash).toString(36);
    }

    verifyPassword(input, hashed) {
        return this.hashPassword(input) === hashed;
    }

    // ==================== PENGAJUAN OPERATIONS ====================

    createPengajuan(id_masyarakat, id_pelayanan, data_formulir, catatan = '') {
        const pengajuan = {
            id_pengajuan: this.generateId(),
            id_masyarakat,
            id_pelayanan,
            tanggal_pengajuan: new Date().toISOString(),
            status: 'DIAJUKAN',
            catatan,
            data_formulir,
            catatan_revisi: '',
            updated_at: new Date().toISOString()
        };
        this.insert('pengajuan', pengajuan);
        return pengajuan;
    }

    getPengajuanByMasyarakat(id_masyarakat) {
        return this.findWhere('pengajuan', { id_masyarakat })
            .sort((a, b) => new Date(b.tanggal_pengajuan) - new Date(a.tanggal_pengajuan));
    }

    getPengajuanByStatus(status) {
        return this.findWhere('pengajuan', { status })
            .sort((a, b) => new Date(b.tanggal_pengajuan) - new Date(a.tanggal_pengajuan));
    }

    updateStatusPengajuan(id_pengajuan, status, catatan = '') {
        const updates = { status, updated_at: new Date().toISOString() };
        if (catatan) updates.catatan_revisi = catatan;
        return this.update('pengajuan', 'id_pengajuan', id_pengajuan, updates);
    }

    // ==================== DOKUMEN OPERATIONS ====================

    addDokumen(id_pengajuan, nama_dokumen, file_data) {
        const dokumen = {
            id_dokumen: this.generateId(),
            id_pengajuan,
            nama_dokumen,
            file_dokumen: file_data,
            tanggal_upload: new Date().toISOString()
        };
        this.insert('dokumen', dokumen);
        return dokumen;
    }

    getDokumenByPengajuan(id_pengajuan) {
        return this.findWhere('dokumen', { id_pengajuan });
    }

    // ==================== VERIFIKASI OPERATIONS ====================

    createVerifikasi(id_pengajuan, id_petugas, hasil, catatan = '') {
        const verifikasi = {
            id_verifikasi: this.generateId(),
            id_pengajuan,
            id_petugas,
            tanggal_verifikasi: new Date().toISOString(),
            hasil,
            catatan
        };
        this.insert('verifikasi', verifikasi);
        return verifikasi;
    }

    // ==================== SURAT OPERATIONS ====================

    createSurat(id_pengajuan, nomor_surat) {
        const surat = {
            id_surat: this.generateId(),
            id_pengajuan,
            nomor_surat,
            tanggal_surat: new Date().toISOString(),
            file_surat: null
        };
        this.insert('surat', surat);
        return surat;
    }

    getSuratByPengajuan(id_pengajuan) {
        return this.findWhere('surat', { id_pengajuan })[0] || null;
    }

    // ==================== NOTIFIKASI OPERATIONS ====================

    createNotifikasi(id_user, pesan) {
        const notif = {
            id_notifikasi: this.generateId(),
            id_user,
            pesan,
            tanggal: new Date().toISOString(),
            status_baca: false
        };
        this.insert('notifikasi', notif);
        return notif;
    }

    getNotifikasiByUser(id_user) {
        return this.findWhere('notifikasi', { id_user })
            .sort((a, b) => new Date(b.tanggal) - new Date(a.tanggal));
    }

    getUnreadCount(id_user) {
        return this.findWhere('notifikasi', { id_user, status_baca: false }).length;
    }

    markNotifikasiRead(id_notifikasi) {
        return this.update('notifikasi', 'id_notifikasi', id_notifikasi, { status_baca: true });
    }

    // ==================== PELAYANAN HELPERS ====================

    getPelayananById(id) {
        return this.getById('jenis_pelayanan', 'id_pelayanan', id);
    }

    // ==================== REPORTING ====================

    getRekapPengajuan() {
        const all = this.getAll('pengajuan');
        const statuses = ['DIAJUKAN', 'DIPERIKSA', 'REVISI', 'DIVERIFIKASI', 'MENUNGGU_PERSETUJUAN', 'DISETUJUI', 'DITOLAK', 'DIPROSES', 'SELESAI'];
        const rekap = {};
        statuses.forEach(s => { rekap[s] = all.filter(p => p.status === s).length; });
        rekap.TOTAL = all.length;
        return rekap;
    }

    getRekapPerPelayanan() {
        const pelayanan = this.getAll('jenis_pelayanan');
        const pengajuan = this.getAll('pengajuan');
        return pelayanan.map(p => ({
            ...p,
            total: pengajuan.filter(pj => pj.id_pelayanan === p.id_pelayanan).length,
            selesai: pengajuan.filter(pj => pj.id_pelayanan === p.id_pelayanan && pj.status === 'SELESAI').length
        }));
    }

    // ==================== EXPORT / IMPORT ====================

    exportAllData() {
        const data = {};
        this.tables.forEach(t => { data[t] = this.getAll(t); });
        return JSON.stringify(data, null, 2);
    }

    importData(jsonString) {
        try {
            const data = JSON.parse(jsonString);
            this.tables.forEach(t => {
                if (data[t]) localStorage.setItem(this._key(t), JSON.stringify(data[t]));
            });
            return true;
        } catch (e) {
            console.error('Import failed:', e);
            return false;
        }
    }

    // ==================== STATISTICS ====================

    getStatistik() {
        const pengajuan = this.getAll('pengajuan');
        const now = new Date();
        const thisMonth = pengajuan.filter(p => {
            const d = new Date(p.tanggal_pengajuan);
            return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
        });
        const lastMonth = pengajuan.filter(p => {
            const d = new Date(p.tanggal_pengajuan);
            const lm = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            return d.getMonth() === lm.getMonth() && d.getFullYear() === lm.getFullYear();
        });
        return {
            totalBulanIni: thisMonth.length,
            totalBulanLalu: lastMonth.length,
            selesaiBulanIni: thisMonth.filter(p => p.status === 'SELESAI').length,
            rataRataPerHari: (thisMonth.length / Math.max(now.getDate(), 1)).toFixed(1)
        };
    }

    // ==================== SEED DATA ====================

    seed() {
        // Clear all
        this.tables.forEach(t => localStorage.removeItem(this._key(t)));

        // === Default Admin ===
        const adminUser = {
            id_user: 'usr_admin',
            nama: 'Administrator',
            email: 'admin@sipades.go.id',
            password: this.hashPassword('admin123'),
            role: 'admin',
            created_at: new Date().toISOString()
        };
        this.insert('users', adminUser);

        // === Default Petugas ===
        const petugasUser = {
            id_user: 'usr_petugas1',
            nama: 'Ahmad Fauzi',
            email: 'petugas@sipades.go.id',
            password: this.hashPassword('petugas123'),
            role: 'petugas',
            created_at: new Date().toISOString()
        };
        this.insert('users', petugasUser);
        this.insert('petugas', {
            id_petugas: 'ptg_1',
            id_user: 'usr_petugas1',
            jabatan: 'Staf Pelayanan'
        });

        // === Default Kepala Desa ===
        const kepalaUser = {
            id_user: 'usr_kepala1',
            nama: 'H. Muhammad Rizki, S.Sos',
            email: 'kepala@sipades.go.id',
            password: this.hashPassword('kepala123'),
            role: 'kepala',
            created_at: new Date().toISOString()
        };
        this.insert('users', kepalaUser);
        this.insert('kepala_desa_lurah', {
            id_pimpinan: 'kpl_1',
            id_user: 'usr_kepala1',
            jabatan: 'Kepala Desa'
        });

        // === Default Masyarakat (demo) ===
        const masyUser = {
            id_user: 'usr_masy1',
            nama: 'Budi Santoso',
            email: 'budi@email.com',
            password: this.hashPassword('masyarakat123'),
            role: 'masyarakat',
            created_at: new Date().toISOString()
        };
        this.insert('users', masyUser);
        this.insert('masyarakat', {
            id_masyarakat: 'msy_1',
            id_user: 'usr_masy1',
            nik: '1401012345670001',
            alamat: 'Jl. Merpati No. 12, RT 03/RW 01',
            no_hp: '081234567890'
        });

        // === Jenis Pelayanan (6 jenis sesuai ruang lingkup) ===
        const pelayananData = [
            {
                id_pelayanan: 'ply_domisili',
                nama_pelayanan: 'Surat Keterangan Domisili',
                deskripsi: 'Surat yang menerangkan bahwa seseorang bertempat tinggal di wilayah desa/kelurahan tertentu.',
                persyaratan: 'KTP,Kartu Keluarga (KK),Surat Pengantar RT/RW',
                icon: 'fa-house',
                formulir: JSON.stringify([
                    { name: 'nama_lengkap', label: 'Nama Lengkap', type: 'text', required: true },
                    { name: 'nik', label: 'NIK', type: 'text', required: true },
                    { name: 'tempat_lahir', label: 'Tempat Lahir', type: 'text', required: true },
                    { name: 'tanggal_lahir', label: 'Tanggal Lahir', type: 'date', required: true },
                    { name: 'jenis_kelamin', label: 'Jenis Kelamin', type: 'select', options: ['Laki-laki', 'Perempuan'], required: true },
                    { name: 'agama', label: 'Agama', type: 'select', options: ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'], required: true },
                    { name: 'pekerjaan', label: 'Pekerjaan', type: 'text', required: true },
                    { name: 'alamat_lengkap', label: 'Alamat Lengkap', type: 'textarea', required: true },
                    { name: 'keperluan', label: 'Keperluan', type: 'textarea', required: true }
                ])
            },
            {
                id_pelayanan: 'ply_usaha',
                nama_pelayanan: 'Surat Keterangan Usaha',
                deskripsi: 'Surat yang menerangkan bahwa seseorang memiliki usaha di wilayah desa/kelurahan.',
                persyaratan: 'KTP,Kartu Keluarga (KK),Foto Tempat Usaha,Surat Pengantar RT/RW',
                icon: 'fa-briefcase',
                formulir: JSON.stringify([
                    { name: 'nama_lengkap', label: 'Nama Lengkap', type: 'text', required: true },
                    { name: 'nik', label: 'NIK', type: 'text', required: true },
                    { name: 'alamat', label: 'Alamat', type: 'textarea', required: true },
                    { name: 'nama_usaha', label: 'Nama Usaha', type: 'text', required: true },
                    { name: 'jenis_usaha', label: 'Jenis Usaha', type: 'text', required: true },
                    { name: 'alamat_usaha', label: 'Alamat Usaha', type: 'textarea', required: true },
                    { name: 'tahun_berdiri', label: 'Tahun Berdiri', type: 'number', required: true },
                    { name: 'keperluan', label: 'Keperluan', type: 'textarea', required: true }
                ])
            },
            {
                id_pelayanan: 'ply_tidak_mampu',
                nama_pelayanan: 'Surat Keterangan Tidak Mampu',
                deskripsi: 'Surat yang menerangkan kondisi ekonomi seseorang yang tergolong kurang mampu.',
                persyaratan: 'KTP,Kartu Keluarga (KK),Surat Pengantar RT/RW',
                icon: 'fa-hand-holding-heart',
                formulir: JSON.stringify([
                    { name: 'nama_lengkap', label: 'Nama Lengkap', type: 'text', required: true },
                    { name: 'nik', label: 'NIK', type: 'text', required: true },
                    { name: 'tempat_lahir', label: 'Tempat Lahir', type: 'text', required: true },
                    { name: 'tanggal_lahir', label: 'Tanggal Lahir', type: 'date', required: true },
                    { name: 'jenis_kelamin', label: 'Jenis Kelamin', type: 'select', options: ['Laki-laki', 'Perempuan'], required: true },
                    { name: 'pekerjaan', label: 'Pekerjaan', type: 'text', required: true },
                    { name: 'alamat', label: 'Alamat Lengkap', type: 'textarea', required: true },
                    { name: 'penghasilan', label: 'Penghasilan Per Bulan (Rp)', type: 'number', required: true },
                    { name: 'keperluan', label: 'Keperluan', type: 'textarea', required: true }
                ])
            },
            {
                id_pelayanan: 'ply_nikah',
                nama_pelayanan: 'Surat Pengantar Nikah',
                deskripsi: 'Surat pengantar dari desa/kelurahan sebagai salah satu persyaratan pendaftaran nikah.',
                persyaratan: 'KTP,Kartu Keluarga (KK),Akta Kelahiran,Foto 2x3 dan 4x6,Surat Pengantar RT/RW',
                icon: 'fa-ring',
                formulir: JSON.stringify([
                    { name: 'nama_lengkap', label: 'Nama Lengkap', type: 'text', required: true },
                    { name: 'nik', label: 'NIK', type: 'text', required: true },
                    { name: 'tempat_lahir', label: 'Tempat Lahir', type: 'text', required: true },
                    { name: 'tanggal_lahir', label: 'Tanggal Lahir', type: 'date', required: true },
                    { name: 'jenis_kelamin', label: 'Jenis Kelamin', type: 'select', options: ['Laki-laki', 'Perempuan'], required: true },
                    { name: 'agama', label: 'Agama', type: 'select', options: ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'], required: true },
                    { name: 'pekerjaan', label: 'Pekerjaan', type: 'text', required: true },
                    { name: 'alamat', label: 'Alamat', type: 'textarea', required: true },
                    { name: 'nama_calon', label: 'Nama Calon Pasangan', type: 'text', required: true },
                    { name: 'rencana_tanggal', label: 'Rencana Tanggal Nikah', type: 'date', required: true }
                ])
            },
            {
                id_pelayanan: 'ply_kelahiran',
                nama_pelayanan: 'Surat Keterangan Kelahiran',
                deskripsi: 'Surat yang menerangkan telah lahirnya seorang anak di wilayah desa/kelurahan.',
                persyaratan: 'KTP Orang Tua,Kartu Keluarga (KK),Surat Keterangan Lahir dari Bidan/RS,Buku Nikah/Akta Nikah',
                icon: 'fa-baby',
                formulir: JSON.stringify([
                    { name: 'nama_bayi', label: 'Nama Bayi', type: 'text', required: true },
                    { name: 'jenis_kelamin', label: 'Jenis Kelamin', type: 'select', options: ['Laki-laki', 'Perempuan'], required: true },
                    { name: 'tempat_lahir', label: 'Tempat Lahir', type: 'text', required: true },
                    { name: 'tanggal_lahir', label: 'Tanggal Lahir', type: 'date', required: true },
                    { name: 'nama_ayah', label: 'Nama Ayah', type: 'text', required: true },
                    { name: 'nik_ayah', label: 'NIK Ayah', type: 'text', required: true },
                    { name: 'nama_ibu', label: 'Nama Ibu', type: 'text', required: true },
                    { name: 'nik_ibu', label: 'NIK Ibu', type: 'text', required: true },
                    { name: 'alamat', label: 'Alamat', type: 'textarea', required: true }
                ])
            },
            {
                id_pelayanan: 'ply_kematian',
                nama_pelayanan: 'Surat Keterangan Kematian',
                deskripsi: 'Surat yang menerangkan telah meninggalnya seseorang di wilayah desa/kelurahan.',
                persyaratan: 'KTP Almarhum/ah,Kartu Keluarga (KK),KTP Pelapor,Surat Keterangan dari RS/Dokter (jika ada),Surat Pengantar RT/RW',
                icon: 'fa-scroll',
                formulir: JSON.stringify([
                    { name: 'nama_almarhum', label: 'Nama Almarhum/ah', type: 'text', required: true },
                    { name: 'nik', label: 'NIK Almarhum/ah', type: 'text', required: true },
                    { name: 'tempat_lahir', label: 'Tempat Lahir', type: 'text', required: true },
                    { name: 'tanggal_lahir', label: 'Tanggal Lahir', type: 'date', required: true },
                    { name: 'jenis_kelamin', label: 'Jenis Kelamin', type: 'select', options: ['Laki-laki', 'Perempuan'], required: true },
                    { name: 'alamat', label: 'Alamat Terakhir', type: 'textarea', required: true },
                    { name: 'tanggal_meninggal', label: 'Tanggal Meninggal', type: 'date', required: true },
                    { name: 'tempat_meninggal', label: 'Tempat Meninggal', type: 'text', required: true },
                    { name: 'sebab_kematian', label: 'Sebab Kematian', type: 'text', required: true },
                    { name: 'nama_pelapor', label: 'Nama Pelapor', type: 'text', required: true },
                    { name: 'hubungan_pelapor', label: 'Hubungan dengan Almarhum/ah', type: 'text', required: true }
                ])
            }
        ];
        pelayananData.forEach(p => this.insert('jenis_pelayanan', p));

        // === Sample Pengajuan (demo data) ===
        const samplePengajuan = {
            id_pengajuan: 'pjn_sample1',
            id_masyarakat: 'msy_1',
            id_pelayanan: 'ply_domisili',
            tanggal_pengajuan: new Date(Date.now() - 3 * 86400000).toISOString(),
            status: 'SELESAI',
            catatan: '',
            data_formulir: JSON.stringify({
                nama_lengkap: 'Budi Santoso',
                nik: '1401012345670001',
                tempat_lahir: 'Pekanbaru',
                tanggal_lahir: '1990-05-15',
                jenis_kelamin: 'Laki-laki',
                agama: 'Islam',
                pekerjaan: 'Wiraswasta',
                alamat_lengkap: 'Jl. Merpati No. 12, RT 03/RW 01',
                keperluan: 'Keperluan administrasi'
            }),
            catatan_revisi: '',
            updated_at: new Date().toISOString()
        };
        this.insert('pengajuan', samplePengajuan);

        this.insert('surat', {
            id_surat: 'srt_sample1',
            id_pengajuan: 'pjn_sample1',
            nomor_surat: '001/SKD/DS/X/2026',
            tanggal_surat: new Date().toISOString(),
            file_surat: 'generated'
        });

        // === Second Masyarakat (demo) ===
        const masyUser2 = {
            id_user: 'usr_masy2',
            nama: 'Siti Rahmawati',
            email: 'siti@email.com',
            password: this.hashPassword('masyarakat123'),
            role: 'masyarakat',
            created_at: new Date(Date.now() - 5 * 86400000).toISOString()
        };
        this.insert('users', masyUser2);
        this.insert('masyarakat', {
            id_masyarakat: 'msy_2',
            id_user: 'usr_masy2',
            nik: '1401012345670002',
            alamat: 'Jl. Kenanga No. 5, RT 02/RW 03',
            no_hp: '085678901234'
        });

        // === Sample Pengajuan 2 - Status MENUNGGU PERSETUJUAN ===
        this.insert('pengajuan', {
            id_pengajuan: 'pjn_sample2',
            id_masyarakat: 'msy_2',
            id_pelayanan: 'ply_usaha',
            tanggal_pengajuan: new Date(Date.now() - 1 * 86400000).toISOString(),
            status: 'MENUNGGU_PERSETUJUAN',
            catatan: '',
            data_formulir: JSON.stringify({
                nama_lengkap: 'Siti Rahmawati',
                nik: '1401012345670002',
                alamat: 'Jl. Kenanga No. 5, RT 02/RW 03',
                nama_usaha: 'Warung Makan Berkah',
                jenis_usaha: 'Kuliner',
                alamat_usaha: 'Jl. Kenanga No. 5A',
                tahun_berdiri: '2020',
                keperluan: 'Pengajuan izin usaha'
            }),
            catatan_revisi: '',
            updated_at: new Date().toISOString()
        });

        this.insert('verifikasi', {
            id_verifikasi: 'ver_sample1',
            id_pengajuan: 'pjn_sample2',
            id_petugas: 'ptg_1',
            tanggal_verifikasi: new Date(Date.now() - 0.5 * 86400000).toISOString(),
            hasil: 'TERVERIFIKASI',
            catatan: 'Data dan dokumen telah diperiksa dan lengkap.'
        });

        // === Sample Pengajuan 3 - Status DIAJUKAN ===
        this.insert('pengajuan', {
            id_pengajuan: 'pjn_sample3',
            id_masyarakat: 'msy_1',
            id_pelayanan: 'ply_tidak_mampu',
            tanggal_pengajuan: new Date(Date.now() - 0.25 * 86400000).toISOString(),
            status: 'DIAJUKAN',
            catatan: 'Untuk keperluan beasiswa anak',
            data_formulir: JSON.stringify({
                nama_lengkap: 'Budi Santoso',
                nik: '1401012345670001',
                tempat_lahir: 'Pekanbaru',
                tanggal_lahir: '1990-05-15',
                jenis_kelamin: 'Laki-laki',
                pekerjaan: 'Buruh Harian',
                alamat: 'Jl. Merpati No. 12, RT 03/RW 01',
                penghasilan: '1500000',
                keperluan: 'Beasiswa pendidikan anak'
            }),
            catatan_revisi: '',
            updated_at: new Date().toISOString()
        });

        // === Sample Notifikasi ===
        this.createNotifikasi('usr_masy1', 'Selamat datang di SIPADES! Anda dapat mengajukan pelayanan administrasi desa melalui sistem ini.');
        this.createNotifikasi('usr_masy1', 'Surat Keterangan Domisili Anda telah selesai dan siap diunduh.');
        this.createNotifikasi('usr_petugas1', 'Pengajuan baru: Surat Keterangan Tidak Mampu dari Budi Santoso.');
        this.createNotifikasi('usr_kepala1', 'Pengajuan Surat Keterangan Usaha dari Siti Rahmawati menunggu persetujuan Anda.');

        localStorage.setItem(this.prefix + 'initialized', 'true');
        console.log('[SIPADES] Database initialized with seed data.');
    }

    // ==================== RESET ====================
    reset() {
        this.tables.forEach(t => localStorage.removeItem(this._key(t)));
        localStorage.removeItem(this.prefix + 'initialized');
        this.seed();
    }
}

// Global instance
const db = new Database();
