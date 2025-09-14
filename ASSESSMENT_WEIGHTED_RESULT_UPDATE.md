# Update: Hasil Penilaian Berbobot

## Perubahan yang Dibuat

### 1. Modal Assessment Scores (`assessment-scores.blade.php`)
- **Sebelum**: "Skor Rata-rata" menampilkan rata-rata sederhana dari semua skor
- **Sesudah**: "Hasil Penilaian" menampilkan total skor berbobot dari semua kategori

### 2. Controller Export (`AssessmentExportController.php`) 
- Menambah perhitungan `$totalWeightedScore` yang menjumlahkan skor berbobot dari setiap kategori
- Mengirim variabel `totalWeightedScore` ke template PDF

### 3. Template PDF (`assessment-report.blade.php`)
- **Sebelum**: "Skor Rata-rata" dengan nilai `$averageScore`
- **Sesudah**: "Hasil Penilaian" dengan nilai `$totalWeightedScore` dan subtitle "Skor Berbobot Total"

## Algoritma Perhitungan Skor Berbobot

```php
$totalWeightedScore = 0;
foreach ($assessmentScores as $categoryName => $scores) {
    if ($scores->isNotEmpty()) {
        // Ambil bobot kategori dari indikator pertama
        $categoryWeight = $firstScore->assessmentIndicator->category->bobot_penilaian;
        
        // Hitung rata-rata skor kategori
        $categoryAverage = $scores->avg('skor');
        
        // Hitung skor berbobot: rata-rata × (bobot ÷ 100)
        $weightedCategoryScore = $categoryAverage * ($categoryWeight / 100);
        
        // Tambahkan ke total
        $totalWeightedScore += $weightedCategoryScore;
    }
}
```

## Contoh Perhitungan

Jika ada 3 kategori:
- **Kategori A**: Rata-rata 3.5, Bobot 30% → Skor Berbobot = 3.5 × 0.3 = 1.05
- **Kategori B**: Rata-rata 3.0, Bobot 40% → Skor Berbobot = 3.0 × 0.4 = 1.20  
- **Kategori C**: Rata-rata 2.8, Bobot 30% → Skor Berbobot = 2.8 × 0.3 = 0.84

**Total Hasil Penilaian** = 1.05 + 1.20 + 0.84 = **3.09**

## Manfaat

1. **Lebih Akurat**: Mempertimbangkan bobot relatif setiap kategori assessment
2. **Proporsional**: Kategori dengan bobot lebih tinggi berkontribusi lebih besar
3. **Transparan**: Perhitungan skor berbobot ditampilkan di setiap kategori
4. **Konsisten**: Sama antara tampilan modal dan laporan PDF

## Lokasi File yang Diubah

- `/resources/views/filament/modals/assessment-scores.blade.php`
- `/app/Http/Controllers/AssessmentExportController.php`  
- `/resources/views/exports/assessment-report.blade.php`
