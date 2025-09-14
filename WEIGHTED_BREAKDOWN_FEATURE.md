# Update: Breakdown Skor Berbobot Per Kategori

## Perubahan yang Ditambahkan

### 1. Modal Assessment Scores
**Fitur Baru**: Tabel "Breakdown Skor Berbobot Per Kategori" yang menampilkan:

| Kolom | Deskripsi |
|-------|-----------|
| **Kategori** | Nama kategori assessment + jumlah indikator |
| **Rata-rata Skor** | Skor rata-rata semua indikator dalam kategori |
| **Bobot (%)** | Persentase bobot kategori dalam total penilaian |
| **Skor Berbobot** | Hasil perhitungan: Rata-rata × (Bobot ÷ 100) |
| **Kontribusi (%)** | Persentase kontribusi kategori terhadap total skor berbobot |

**Footer Tabel**: Menampilkan total skor berbobot dan total kontribusi (100%)

### 2. Laporan PDF Assessment
**Fitur Baru**: Tabel breakdown yang sama seperti modal, dengan format PDF-friendly

### 3. Formula Perhitungan
Ditampilkan di bawah tabel untuk transparansi:
- **Skor Berbobot** = Rata-rata Skor Kategori × (Bobot Kategori ÷ 100)
- **Total Hasil Penilaian** = Σ (Semua Skor Berbobot Kategori)
- **Kontribusi** = (Skor Berbobot Kategori ÷ Total Skor Berbobot) × 100%

## Contoh Hasil Tampilan

### Breakdown Skor Berbobot Per Kategori

| Kategori | Rata-rata Skor | Bobot (%) | Skor Berbobot | Kontribusi (%) |
|----------|---------------|-----------|---------------|----------------|
| Standar Isi dan Kurikulum | 3.50 | 25.0% | 0.875 | 27.1% |
| Standar Proses Pembelajaran | 3.20 | 20.0% | 0.640 | 19.8% |
| Standar Kompetensi Lulusan | 3.80 | 20.0% | 0.760 | 23.5% |
| Standar Pendidik & Tenaga Kependidikan | 3.00 | 15.0% | 0.450 | 13.9% |
| Standar Sarana dan Prasarana | 2.90 | 10.0% | 0.290 | 9.0% |
| Standar Pengelolaan | 3.10 | 10.0% | 0.310 | 9.6% |
| **TOTAL SKOR BERBOBOT** | | | **3.235** | **100.0%** |

## Manfaat Fitur

### 1. **Transparansi Perhitungan**
- Menampilkan detail bagaimana skor berbobot dihitung
- Memperlihatkan kontribusi setiap kategori terhadap hasil akhir
- Formula perhitungan jelas dan mudah dipahami

### 2. **Analisis Mendalam**
- Identifikasi kategori mana yang memberikan kontribusi terbesar/terkecil
- Perbandingan antara bobot kategori dengan performa aktual
- Memudahkan identifikasi area yang perlu diperbaiki

### 3. **Akuntabilitas**
- Hasil penilaian dapat dipertanggungjawabkan dengan detail perhitungan
- Stakeholder dapat memahami bagaimana nilai akhir didapat
- Mendukung pengambilan keputusan berbasis data

### 4. **Konsistensi**
- Tampilan sama antara modal web dan laporan PDF
- Perhitungan menggunakan formula yang konsisten
- Data real-time dan akurat

## Visualisasi

### Modal Web
- Tabel interaktif dengan hover effects
- Progress bar untuk visualisasi kontribusi
- Color coding untuk identifikasi cepat
- Icons dan styling yang user-friendly

### Laporan PDF
- Tabel terstruktur dengan border dan shading
- Font size optimal untuk printing
- Format yang clean dan professional
- Informasi formula di bagian bawah

## Lokasi File yang Diubah

- `/resources/views/filament/modals/assessment-scores.blade.php`
- `/resources/views/exports/assessment-report.blade.php`

## Contoh Implementasi dalam Kode

```php
// Perhitungan Skor Berbobot
$categoryAverage = $scores->avg('skor');
$categoryWeight = $category->bobot_penilaian;
$weightedCategoryScore = $categoryAverage * ($categoryWeight / 100);

// Perhitungan Kontribusi
$contribution = ($weightedCategoryScore / $totalWeightedScore) * 100;

// Total Skor Berbobot
$totalWeightedScore = Σ($weightedCategoryScore);
```

Fitur ini memberikan visibilitas penuh terhadap bagaimana setiap kategori berkontribusi pada hasil penilaian akhir, membuat sistem assessment menjadi lebih transparan dan mudah dipahami.
