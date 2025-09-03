# 🎓 SIMAK - Sistem Informasi Manajemen Asesmen Kinerja Sekolah

[![Laravel](https://img.shields.io/badge/Laravel-11+-red.svg)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-v3-orange.svg)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)

Sistem informasi berbasis web untuk mengelola asesmen kinerja sekolah dengan menggunakan Laravel 12, Filament v3, dan Filament Shield untuk manajemen permission.

## 🚀 Fitur Utama

### 📊 Dashboard & Statistik
- Overview real-time jumlah sekolah, kategori asesmen, dan status asesmen
- Widget statistik dengan visual indicators
- Dashboard responsif dan user-friendly

### 🏫 Manajemen Master Data
- **Data Sekolah**: CRUD lengkap dengan informasi NPSN, alamat, jenjang, dan status
- **Kategori Asesmen**: Pengelolaan kategori berdasarkan komponen (Siswa, Guru, Kinerja Guru, Management Kepala Sekolah)
- **Indikator Asesmen**: Detail indikator dengan bobot, kriteria penilaian, dan skor maksimal
- **Data Asesor**: Manajemen profil asesor dengan wilayah kerja
- **Periode Asesmen**: Pengaturan tahun ajaran dan semester

### 🔐 Sistem Permission & Role
- Role-based access control dengan Filament Shield
- Granular permissions per resource
- Super admin dengan akses penuh
- Policy-based authorization

### 📋 Sistem Asesmen (Foundation)
- Struktur database untuk scoring dan workflow asesmen
- Support untuk bukti dukung dan catatan
- Status tracking (draft → submitted → reviewed → approved)
- Grading system (A, B, C, D)

## 🛠️ Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Admin Panel**: Filament v3
- **Database**: SQLite (development) / MySQL/PostgreSQL (production)
- **Permission**: Spatie Laravel Permission + Filament Shield
- **Frontend**: Livewire 3 (via Filament)
- **Styling**: Tailwind CSS (via Filament)

## 📦 Instalasi

### Prasyarat
- PHP 8.2 atau lebih tinggi
- Composer
- Node.js & NPM
- Git

### Langkah Instalasi

1. **Clone Repository**
```bash
git clone https://github.com/pindoyono/simak.git
cd simak
```

2. **Install Dependencies**
```bash
composer install
npm install && npm run build
```

3. **Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Database Setup**
```bash
# Edit .env file untuk konfigurasi database
php artisan migrate
php artisan db:seed
```

5. **Buat Admin User**
```bash
php artisan make:filament-user
php artisan shield:super-admin
```

6. **Generate Permissions**
```bash
php artisan shield:generate --all
```

7. **Jalankan Server**
```bash
php artisan serve
```

Akses admin panel di: `http://localhost:8000/admin`

## 🗂️ Struktur Database

### Tabel Utama
- `schools` - Data sekolah
- `assessment_categories` - Kategori asesmen dengan komponen
- `assessment_indicators` - Indikator penilaian
- `assessment_periods` - Periode asesmen
- `assessors` - Data asesor
- `school_assessments` - Asesmen sekolah
- `assessment_scores` - Skor per indikator
- `assessment_reports` - Laporan asesmen

### Komponen Asesmen
1. **SISWA** - Standar terkait pencapaian siswa
2. **GURU** - Standar terkait kualitas guru
3. **KINERJA GURU DALAM MENGELOLA PROSES PEMBELAJARAN** - Evaluasi kinerja mengajar
4. **MANAGEMENT KEPALA SEKOLAH** - Kepemimpinan dan pengelolaan sekolah

## 📱 Penggunaan

### Login Admin
- Email: `superadmin@admin.com` (atau sesuai yang dibuat)
- Password: sesuai yang dibuat saat setup

### Menu Utama
1. **Dashboard** - Overview statistik sistem
2. **Schools** - Manajemen data sekolah
3. **Assessment Categories** - Kategori asesmen
4. **Assessment Indicators** - Indikator penilaian
5. **Assessors** - Data asesor
6. **Shield** - Manajemen role & permission

## 🔄 Development Roadmap

### Phase 1 ✅ (Current)
- [x] Database schema & models
- [x] Basic CRUD operations
- [x] Permission system
- [x] Dashboard widgets
- [x] Data seeding

### Phase 2 (Next)
- [ ] Interactive assessment forms
- [ ] Workflow management (approval process)
- [ ] File upload for evidence
- [ ] Bulk import schools via Excel
- [ ] Advanced filtering & search

### Phase 3 (Future)
- [ ] Report generation (PDF/Excel)
- [ ] Charts & data visualization
- [ ] Email notifications
- [ ] Calendar integration
- [ ] Mobile responsiveness
- [ ] API endpoints

## 🤝 Contributing

1. Fork repository
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 License

Project ini menggunakan [MIT License](LICENSE).

## 🙏 Credits

- [Laravel](https://laravel.com) - PHP Framework
- [Filament](https://filamentphp.com) - Admin Panel
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) - Role & Permission
- [Filament Shield](https://github.com/bezhanSalleh/filament-shield) - Filament Permission Integration

## 📞 Support

Jika ada pertanyaan atau butuh bantuan:
- Buat issue di [GitHub Issues](https://github.com/pindoyono/simak/issues)
- Email: pindoyono@gmail.com

---

**Dibuat dengan ❤️ untuk meningkatkan kualitas pendidikan Indonesia**

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
