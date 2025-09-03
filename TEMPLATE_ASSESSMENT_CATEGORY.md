# Template Import Assessment Category

## Format File Excel

File template Excel untuk import data Assessment Category memiliki kolom-kolom berikut:

| Kolom | Keterangan | Wajib | Nilai yang Valid | Contoh |
|-------|------------|-------|------------------|---------|
| **Komponen** | Komponen asesmen (ENUM) | Ya | SISWA, GURU, KINERJA GURU DALAM MENGELOLA PROSES PEMBELAJARAN, MANAGEMENT KEPALA SEKOLAH | SISWA |
| **Nama Kategori** | Nama kategori dalam komponen | Ya | - | Standar Isi dan Kurikulum |
| **Deskripsi** | Deskripsi detail kategori | Tidak | - | Penilaian terhadap standar isi dan kurikulum sekolah |
| **Bobot Penilaian (%)** | Bobot dalam persen (0.01-100) | Ya | 0.01 - 100.00 | 15.00 |
| **Urutan** | Urutan tampil kategori | Tidak | >= 1 | 1 |
| **Status** | Status aktif kategori | Tidak | Aktif/Active/1/Ya/Yes/True | Aktif |

## Cara Penggunaan

1. **Download Template**: Klik tombol "Download Template" di halaman Assessment Categories
2. **Isi Data**: Lengkapi data sesuai format yang telah disediakan
3. **Upload File**: Klik tombol "Import Excel" dan pilih file yang sudah diisi
4. **Verifikasi**: Sistem akan memberikan notifikasi hasil import

## Aturan Validasi

- **Komponen**: Wajib, harus salah satu dari nilai ENUM yang valid:
  - `SISWA`
  - `GURU` 
  - `KINERJA GURU DALAM MENGELOLA PROSES PEMBELAJARAN`
  - `MANAGEMENT KEPALA SEKOLAH`
- **Nama Kategori**: Maksimal 255 karakter, tidak boleh duplikat dalam komponen yang sama
- **Bobot Penilaian**: Harus berupa angka antara 0.01 sampai 100
- **Urutan**: Harus berupa angka positif (minimal 1)
- **Status**: Otomatis "Aktif" jika tidak diisi, atau bisa diisi: Aktif/Active/1/Ya/Yes/True

## Tips

- Pastikan tidak ada baris kosong di tengah data
- Bobot penilaian dalam satu komponen sebaiknya total 100%
- Urutan kategori akan mempengaruhi tampilan di form asesmen
- Data yang duplikat atau error akan dilewati dengan notifikasi

## Error Handling

Sistem akan:
- Menampilkan jumlah data yang berhasil diimport
- Menampilkan jumlah data yang dilewati
- Memberikan detail error jika ada masalah validasi
