# Excel Export Feature for Assessment Reports

## Overview
Added Excel export functionality for assessment reports to complement the existing PDF export feature. This provides users with an alternative format for data analysis and reporting.

## Implementation Details

### 1. Excel Export Class (`/app/Exports/AssessmentDetailExport.php`)

#### Main Export Class
```php
class AssessmentDetailExport implements WithMultipleSheets
```

**Features:**
- **Multi-sheet workbook** with organized data structure
- **Three distinct sheets** for different levels of detail
- **Automatic styling** and formatting for professional appearance
- **Component-based data organization** matching the modal display

#### Sheet Structure

##### Sheet 1: Ringkasan Assessment
- **Purpose**: Executive summary with key metrics
- **Content**:
  - School information (nama sekolah, NPSN, alamat)
  - Assessment information (periode, tanggal, assessor, status)
  - Overall results (total skor, rata-rata, hasil penilaian berbobot, nilai keseluruhan)
  - Generation timestamp

##### Sheet 2: Breakdown Per Komponen
- **Purpose**: Component-level analysis with aggregated data
- **Content**:
  - **Component Summary Table**: Shows aggregated metrics per component
  - **Detail Categories**: Breakdown of categories within each component
  - **Component mapping** to standardized display names
  - **Calculation formulas** and methodology

##### Sheet 3: Detail Skor Per Indikator
- **Purpose**: Granular indicator-level data
- **Content**:
  - **Detailed scores** for every indicator
  - **Category grouping** with individual indicator breakdown
  - **Grade mapping** and score interpretation
  - **Category summaries** with averages and grades

### 2. Controller Enhancement (`/app/Http/Controllers/AssessmentExportController.php`)

#### Added Method
```php
public function exportExcel($schoolAssessmentId)
```

**Features:**
- **Error handling** with try-catch blocks
- **Consistent filename** format matching PDF export
- **Laravel Excel integration** for download response
- **Logging** for debugging and monitoring

### 3. Route Configuration (`/routes/web.php`)

#### Added Route
```php
Route::get('/assessment/export-excel/{schoolAssessment}', [AssessmentExportController::class, 'exportExcel'])
    ->name('assessment.export-excel');
```

**Security:**
- Protected by `auth` middleware
- Parameter type hinting for `SchoolAssessment`
- Consistent naming with PDF export route

### 4. UI Integration

#### Filament Resource Update (`/app/Filament/Resources/SchoolAssessmentResource.php`)
Added Excel export action to the action group:
```php
Tables\Actions\Action::make('export_excel')
    ->label('Export Excel')
    ->icon('heroicon-o-table-cells')
    ->color('success')
    ->url(fn ($record) => route('assessment.export-excel', $record->id))
```

#### Modal Enhancement (`/resources/views/filament/modals/assessment-scores.blade.php`)
Added export buttons in modal header:
- **Export PDF** button (red color, document icon)
- **Export Excel** button (green color, table icon)
- **Responsive design** with proper spacing and hover effects

## Technical Features

### 1. Data Processing
- **Component grouping logic** matches modal display
- **Weighted score calculations** consistent across formats
- **Category mapping** for standardized component names
- **Grade calculation** with proper formatting

### 2. Excel Styling
- **Header formatting** with bold fonts and colored backgrounds
- **Auto-sizing columns** for optimal readability
- **Cell merging** for better visual organization
- **Color-coded sections** for easy navigation

### 3. Performance Considerations
- **Efficient data queries** with proper eager loading
- **Memory-optimized** array building
- **Single database transaction** for consistency

## File Structure

### Excel Export Class
```
app/Exports/AssessmentDetailExport.php
├── AssessmentDetailExport (main class)
├── AssessmentSummarySheet
├── ComponentBreakdownSheet
└── DetailedScoresSheet
```

### Component Name Mapping
```php
$componentDisplayName = match($categoryComponent) {
    'MANAGEMENT KEPALA SEKOLAH' => 'Kepemimpinan Kepala Sekolah',
    'PELANGGAN (SISWA, ORANG TUA DAN MASYARAKAT)' => 'Pelanggan (Siswa, Orang Tua, dan Masyarakat)',
    'PENGUKURAN, ANALISIS DAN MANAGAMEN PENGETAHUAN' => 'Pengukuran, Analisis, dan Manajemen Pengetahuan',
    'TENAGA KERJA (TENAGA PENDIDIK DAN KEPENDIDIKAN)' => 'Tenaga Kerja (Tenaga Pendidik dan Kependidikan)',
    'PROSES' => 'Proses (Operasional)',
    // Additional mappings...
};
```

## Usage

### From Filament Resource
1. Navigate to School Assessment list
2. Click Actions dropdown for any assessment record
3. Select "Export Excel" option
4. Excel file downloads automatically

### From Assessment Modal
1. Click "Assessment" button to open detail modal
2. Click "Export Excel" button in modal header
3. Excel file downloads with complete assessment data

## Benefits

### 1. Data Analysis
- **Spreadsheet format** enables advanced data analysis
- **Multiple sheets** provide different levels of detail
- **Formulas and calculations** can be extended by users
- **Pivot table compatibility** for further analysis

### 2. Reporting Flexibility
- **Customizable formatting** post-export
- **Easy data manipulation** for presentations
- **Chart creation** capabilities within Excel
- **Data filtering and sorting** options

### 3. Integration
- **Professional appearance** with styled headers and formatting
- **Consistent data structure** with web application
- **Easy sharing** and collaboration capabilities

## Files Modified/Created

### New Files
1. `/app/Exports/AssessmentDetailExport.php` - Main Excel export class

### Modified Files
1. `/app/Http/Controllers/AssessmentExportController.php` - Added Excel export method
2. `/routes/web.php` - Added Excel export route
3. `/app/Filament/Resources/SchoolAssessmentResource.php` - Added Excel export action
4. `/resources/views/filament/modals/assessment-scores.blade.php` - Added export buttons

## Future Enhancements

### 1. Advanced Features
- **Conditional formatting** based on score ranges
- **Charts and graphs** embedded in Excel sheets
- **Data validation** for editable cells
- **Template customization** options

### 2. Export Options
- **Filtered exports** by date range or category
- **Bulk export** for multiple assessments
- **Scheduled exports** for regular reporting
- **Custom format options** (CSV, ODS support)

### 3. User Experience
- **Export progress indicators** for large datasets
- **Email delivery** options for exports
- **Export history** and download management
- **Export templates** for different use cases

---
*Feature completed: Excel export provides comprehensive assessment data in spreadsheet format with multi-sheet organization and professional styling.*
