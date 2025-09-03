# Database Design - Sistem Asesmen Kinerja Sekolah

## Tabel Utama yang Diperlukan:

### 1. schools (Sekolah)
- id
- nama_sekolah
- npsn
- alamat
- kecamatan
- kabupaten_kota
- provinsi
- jenjang (SD/SMP/SMA/SMK)
- status (Negeri/Swasta)
- kepala_sekolah
- telepon
- email
- created_at
- updated_at

### 2. assessment_categories (Kategori Asesmen)
- id
- nama_kategori
- deskripsi
- bobot_penilaian
- urutan
- is_active
- created_at
- updated_at

### 3. assessment_indicators (Indikator Asesmen)
- id
- assessment_category_id
- nama_indikator
- deskripsi
- bobot_indikator
- kriteria_penilaian
- urutan
- is_active
- created_at
- updated_at

### 4. assessment_periods (Periode Asesmen)
- id
- nama_periode
- tahun_ajaran
- semester
- tanggal_mulai
- tanggal_selesai
- status (draft/aktif/selesai)
- created_at
- updated_at

### 5. school_assessments (Asesmen Sekolah)
- id
- school_id
- assessment_period_id
- assessor_id (user_id)
- tanggal_asesmen
- status (draft/submitted/reviewed/approved)
- total_score
- grade (A/B/C/D)
- catatan
- created_at
- updated_at

### 6. assessment_scores (Skor Asesmen)
- id
- school_assessment_id
- assessment_indicator_id
- skor
- bukti_dukung
- catatan
- created_at
- updated_at

### 7. assessment_reports (Laporan Asesmen)
- id
- school_assessment_id
- file_path
- report_type (pdf/excel)
- generated_by
- created_at
- updated_at

### 8. assessors (Asesor)
- id
- user_id
- nip
- nama
- jabatan
- instansi
- wilayah_kerja
- is_active
- created_at
- updated_at
