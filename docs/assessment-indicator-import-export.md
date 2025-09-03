# Assessment Indicator Import/Export Documentation

## Overview
Sistem import/export untuk Assessment Indicator memungkinkan admin untuk:
- Download template Excel dengan format yang benar
- Import data Assessment Indicator dari file Excel
- Export data Assessment Indicator yang sudah ada ke Excel

## Files Structure

### Import Class
- **File**: `app/Imports/AssessmentIndicatorImport.php`
- **Purpose**: Handle import data dari Excel ke database
- **Features**:
  - Validasi data sebelum import
  - Foreign key validation untuk Assessment Category
  - Boolean parsing untuk field is_active
  - Batch processing untuk performa optimal

### Export Classes
1. **Template Export**
   - **File**: `app/Exports/AssessmentIndicatorTemplateExport.php`
   - **Purpose**: Generate template Excel kosong dengan sample data
   - **Features**: Professional styling, sample data sesuai kategori yang ada

2. **Data Export**
   - **File**: `app/Exports/AssessmentIndicatorExport.php`
   - **Purpose**: Export data existing ke Excel
   - **Features**: Data mapping, professional styling, relasi dengan kategori

### Resource Integration
- **File**: `app/Filament/Resources/AssessmentIndicatorResource/Pages/ListAssessmentIndicators.php`
- **Features**:
  - Export Data button (blue) - export data existing
  - Download Template button (green) - download template kosong
  - Import Data button (yellow) - upload dan import Excel

## Excel Format

### Column Structure
| Column | Field Name | Type | Required | Description |
|--------|------------|------|----------|-------------|
| A | nama_kategori | String | Yes | Nama Assessment Category (harus sudah ada) |
| B | nama_indikator | String | Yes | Nama indikator (max 255 karakter) |
| C | deskripsi | Text | No | Deskripsi indikator |
| D | bobot_indikator | Decimal | No | Bobot dalam persen (0-999.99) |
| E | kriteria_penilaian | Text | No | Kriteria penilaian |
| F | skor_maksimal | Integer | No | Skor maksimal (1-10, default: 4) |
| G | urutan | Integer | No | Urutan indikator (default: 0) |
| H | is_active | Boolean | No | Status aktif (ya/tidak, default: ya) |

### Validation Rules
- **nama_kategori**: Harus sesuai dengan nama kategori yang ada di database
- **nama_indikator**: Required, maksimal 255 karakter
- **bobot_indikator**: Numeric, 0-999.99
- **skor_maksimal**: Integer, 1-10
- **urutan**: Integer, minimal 0
- **is_active**: Accepts: 1, 0, true, false, ya, tidak, aktif, nonaktif

### Available Assessment Categories
Current categories in database:
- Standar Isi dan Kurikulum (Komponen: SISWA)
- Kompetensi Pedagogik (Komponen: GURU)
- Perencanaan Pembelajaran (Komponen: KINERJA GURU DALAM MENGELOLA PROSES PEMBELAJARAN)
- Kepemimpinan Sekolah (Komponen: MANAGEMENT KEPALA SEKOLAH)

## Usage Instructions

### For Users
1. **Download Template**:
   - Klik tombol "Download Template" (hijau)
   - File akan ter-download dengan nama `template-assessment-indicator.xlsx`
   - Template berisi sample data dan format yang benar

2. **Prepare Data**:
   - Buka template di Excel
   - Isi data sesuai format
   - Pastikan nama_kategori sesuai dengan yang tersedia
   - Simpan sebagai .xlsx atau .xls

3. **Import Data**:
   - Klik tombol "Import Data" (kuning)
   - Upload file Excel yang sudah diisi
   - Sistem akan validasi dan import data
   - Notifikasi akan muncul (berhasil/gagal)

4. **Export Data**:
   - Klik tombol "Export Data" (biru)
   - File akan ter-download dengan nama `data-assessment-indicator-YYYY-MM-DD.xlsx`

### Error Handling
- Validation errors akan ditampilkan dengan pesan yang jelas
- File upload terbatas 5MB
- Format file harus .xlsx atau .xls
- Foreign key validation untuk Assessment Category

## Technical Details

### Database Schema
```sql
CREATE TABLE assessment_indicators (
    id BIGINT UNSIGNED PRIMARY KEY,
    assessment_category_id BIGINT UNSIGNED NOT NULL,
    nama_indikator VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    bobot_indikator DECIMAL(5,2) DEFAULT 0,
    kriteria_penilaian TEXT,
    skor_maksimal INTEGER DEFAULT 4,
    urutan INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (assessment_category_id) REFERENCES assessment_categories(id) ON DELETE CASCADE
);
```

### Processing Features
- **Batch Insert**: 100 records per batch untuk performa optimal
- **Chunk Reading**: 100 records per chunk untuk memory efficiency
- **Relationship Loading**: Eager loading kategori untuk menghindari N+1 query
- **File Cleanup**: Temporary upload files dihapus setelah import

## Testing Results
✅ Import class berhasil dibuat dan ditest
✅ Template export berhasil generate sample data
✅ Data export berhasil dengan relasi kategori
✅ Validation rules berjalan dengan benar
✅ Foreign key validation untuk Assessment Category
✅ Boolean parsing untuk field is_active
✅ File upload dan processing dalam Filament Resource
✅ Professional Excel styling dengan borders dan colors
✅ Batch processing untuk performa optimal
