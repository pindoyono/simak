<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Assessment Report - {{ $schoolAssessment->school->nama_sekolah }} | SIMAK-PM</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #2563eb;
            margin: 0 0 10px 0;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0;
            color: #666;
        }

        .info-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            text-align: center;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
        }

        .info-value {
            color: #333;
            font-weight: bold;
        }

        .summary {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            border-left: 4px solid #2563eb;
        }

        .summary h2 {
            text-align: center;
            color: #1565c0;
            margin: 0 0 20px 0;
            font-size: 18px;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-row {
            display: table-row;
        }

        .summary-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            background: white;
            padding: 15px;
            margin: 0 5px;
            border-radius: 5px;
        }

        .summary-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 20px;
            font-weight: bold;
            color: #1565c0;
        }

        .grade-excellent {
            color: #2e7d32;
        }

        .grade-good {
            color: #1976d2;
        }

        .grade-fair {
            color: #f57c00;
        }

        .grade-poor {
            color: #d32f2f;
        }

        .category {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .category-header {
            background: #2563eb;
            color: white;
            padding: 12px;
            margin: 0;
            font-size: 14px;
            font-weight: bold;
        }

        .category-info {
            background: #f1f5f9;
            padding: 8px 12px;
            font-size: 11px;
            color: #64748b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            font-size: 11px;
        }

        th,
        td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
            font-weight: bold;
            color: #374151;
            font-size: 10px;
            text-transform: uppercase;
        }

        .score-cell {
            text-align: center;
            width: 60px;
        }

        .grade-cell {
            text-align: center;
            width: 80px;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            white-space: nowrap;
        }

        .badge-excellent {
            background: #dcfce7;
            color: #166534;
        }

        .badge-good {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-fair {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-poor {
            background: #fee2e2;
            color: #991b1b;
        }

        .category-summary {
            background: #f8fafc;
            padding: 10px;
            border-top: 2px solid #e2e8f0;
        }

        .category-summary-content {
            display: table;
            width: 100%;
        }

        .category-summary-left {
            display: table-cell;
            vertical-align: middle;
            font-weight: bold;
            color: #374151;
        }

        .category-summary-right {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }

        .page-break {
            page-break-before: always;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>📊 Laporan Assessment Kinerja Pendidikan Menengah</h1>
        <p><strong>Sistem Informasi Model Assessment Kinerja Pendidikan Menengah (SIMAK-PM)</strong></p>
        <p style="color: #2563eb; font-weight: bold; font-size: 16px; margin-top: 10px;">
            {{ $schoolAssessment->school->nama_sekolah }}</p>
        <p style="color: #666; font-size: 12px; margin-top: 5px;">Periode: {{ $schoolAssessment->period->nama_periode }}
        </p>
    </div>

    <div class="info-section">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell">
                    <div class="info-label">Sekolah</div>
                    <div class="info-value">{{ $schoolAssessment->school->nama_sekolah }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Periode</div>
                    <div class="info-value">{{ $schoolAssessment->period->nama_periode }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Asesor</div>
                    <div class="info-value">{{ $schoolAssessment->assessor->name }}</div>
                </div>
            </div>
        </div>
    </div>

    @if ($assessmentScores->isNotEmpty())
        <div class="summary">
            <h2>🏆 Ringkasan Penilaian Kinerja Pendidikan</h2>
            <div class="summary-grid">
                <div class="summary-row">
                    <div class="summary-cell">
                        <div class="summary-label">Total Skor</div>
                        <div class="summary-value">{{ number_format($totalScore, 2) }}</div>
                    </div>
                    <div class="summary-cell" style="margin: 0 10px;">
                        <div class="summary-label">Hasil Penilaian</div>
                        <div class="summary-value">{{ number_format($totalWeightedScore, 2) }}</div>
                        <div style="font-size: 10px; color: #666;">Skor Berbobot Total</div>
                    </div>
                    <div class="summary-cell">
                        <div class="summary-label">Nilai Keseluruhan</div>
                        <div
                            class="summary-value
                            @if ($overallGrade === 'Sangat Baik') grade-excellent
                            @elseif($overallGrade === 'Baik') grade-good
                            @elseif($overallGrade === 'Cukup') grade-fair
                            @else grade-poor @endif">
                            {{ $overallGrade }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Weighted Score Breakdown Table for PDF --}}
        <div style="margin: 20px 0;">
            <h3 style="color: #333; margin-bottom: 15px; font-size: 16px;">📊 Breakdown Skor Berbobot Per Kategori</h3>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12px;">
                <thead>
                    <tr style="background-color: #e3f2fd;">
                        <th style="border: 1px solid #ddd; padding: 10px; text-align: left; font-weight: bold;">Kategori
                        </th>
                        <th style="border: 1px solid #ddd; padding: 10px; text-align: center; font-weight: bold;">
                            Rata-rata Skor</th>
                        <th style="border: 1px solid #ddd; padding: 10px; text-align: center; font-weight: bold;">Bobot
                            (%)</th>
                        <th style="border: 1px solid #ddd; padding: 10px; text-align: center; font-weight: bold;">Skor
                            Berbobot</th>
                        <th style="border: 1px solid #ddd; padding: 10px; text-align: center; font-weight: bold;">
                            Kontribusi (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assessmentScores as $categoryName => $scores)
                        @if ($scores->isNotEmpty())
                            @php
                                $firstScore = $scores->first();
                                $categoryWeight =
                                    $firstScore &&
                                    $firstScore->assessmentIndicator &&
                                    $firstScore->assessmentIndicator->category
                                        ? $firstScore->assessmentIndicator->category->bobot_penilaian
                                        : 0;

                                $categoryAverage = $scores->avg('skor');
                                $weightedCategoryScore = $categoryAverage * ($categoryWeight / 100);
                                $contribution =
                                    $totalWeightedScore > 0 ? ($weightedCategoryScore / $totalWeightedScore) * 100 : 0;
                            @endphp
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">
                                    {{ $categoryName }}
                                    <br><small style="color: #666; font-weight: normal;">{{ $scores->count() }}
                                        indikator</small>
                                </td>
                                <td
                                    style="border: 1px solid #ddd; padding: 8px; text-align: center; font-weight: bold;">
                                    {{ number_format($categoryAverage, 2) }}
                                </td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
                                    <span
                                        style="background-color: #e3f2fd; padding: 3px 8px; border-radius: 12px; font-weight: bold;">
                                        {{ number_format($categoryWeight, 1) }}%
                                    </span>
                                </td>
                                <td
                                    style="border: 1px solid #ddd; padding: 8px; text-align: center; color: #1976d2; font-weight: bold; font-size: 14px;">
                                    {{ number_format($weightedCategoryScore, 3) }}
                                </td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
                                    {{ number_format($contribution, 1) }}%
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #bbdefb; font-weight: bold;">
                        <td style="border: 1px solid #ddd; padding: 10px; text-align: left;" colspan="3">
                            <strong>TOTAL SKOR BERBOBOT</strong>
                        </td>
                        <td
                            style="border: 1px solid #ddd; padding: 10px; text-align: center; color: #1565c0; font-size: 16px;">
                            <strong>{{ number_format($totalWeightedScore, 3) }}</strong>
                        </td>
                        <td style="border: 1px solid #ddd; padding: 10px; text-align: center;">
                            <strong>100.0%</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>

            {{-- Calculation Formula --}}
            <div
                style="background-color: #e3f2fd; padding: 12px; border-radius: 6px; border: 1px solid #bbdefb; margin-top: 15px;">
                <h4 style="color: #1565c0; margin-bottom: 8px; font-size: 13px;">ℹ️ Formula Perhitungan:</h4>
                <ul style="color: #1565c0; font-size: 11px; margin: 0; padding-left: 20px;">
                    <li><strong>Skor Berbobot</strong> = Rata-rata Skor Kategori × (Bobot Kategori ÷ 100)</li>
                    <li><strong>Total Hasil Penilaian</strong> = Σ (Semua Skor Berbobot Kategori)</li>
                    <li><strong>Kontribusi</strong> = (Skor Berbobot Kategori ÷ Total Skor Berbobot) × 100%</li>
                </ul>
            </div>
        </div>

        @foreach ($assessmentScores as $categoryName => $scores)
            <div class="category">
                <h3 class="category-header">{{ $categoryName }}</h3>
                <div class="category-info">
                    {{ $scores->count() }} indikator penilaian
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Indikator</th>
                            <th class="score-cell">Skor</th>
                            <th class="grade-cell">Nilai</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($scores as $score)
                            <tr>
                                <td>
                                    <strong>{{ $score->assessmentIndicator->nama_indikator }}</strong>
                                    @if ($score->assessmentIndicator->deskripsi)
                                        <br><small
                                            style="color: #666;">{{ $score->assessmentIndicator->deskripsi }}</small>
                                    @endif
                                </td>
                                <td class="score-cell">
                                    <span
                                        class="badge
                                        @if ($score->skor >= 3.5) badge-excellent
                                        @elseif($score->skor >= 2.5) badge-good
                                        @elseif($score->skor >= 1.5) badge-fair
                                        @else badge-poor @endif">
                                        {{ number_format($score->skor, 2) }}
                                    </span>
                                </td>
                                <td class="grade-cell">
                                    @php
                                        $gradeDisplay = match ($score->grade) {
                                            'Sangat Baik', 'A' => 'Sangat Baik',
                                            'Baik', 'B' => 'Baik',
                                            'Cukup', 'C' => 'Cukup',
                                            'Kurang', 'D' => 'Kurang',
                                            default => $score->grade,
                                        };
                                    @endphp
                                    <span
                                        class="badge
                                        @if (in_array($score->grade, ['Sangat Baik', 'A'])) badge-excellent
                                        @elseif(in_array($score->grade, ['Baik', 'B'])) badge-good
                                        @elseif(in_array($score->grade, ['Cukup', 'C'])) badge-fair
                                        @else badge-poor @endif">
                                        {{ $gradeDisplay }}
                                    </span>
                                </td>
                                <td>{{ $score->catatan ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="category-summary">
                    <div class="category-summary-content">
                        <div class="category-summary-left">
                            Total Skor Kategori:
                        </div>
                        <div class="category-summary-right">
                            @php
                                $categoryTotal = $scores->sum('skor');
                                $categoryAverage = $scores->avg('skor');
                                $categoryGrade = match (true) {
                                    $categoryAverage >= 3.5 => 'Sangat Baik',
                                    $categoryAverage >= 2.5 => 'Baik',
                                    $categoryAverage >= 1.5 => 'Cukup',
                                    default => 'Kurang',
                                };
                            @endphp
                            <strong>{{ number_format($categoryTotal, 2) }}</strong>
                            (Rata-rata: <strong>{{ number_format($categoryAverage, 2) }}</strong>)
                            <span
                                class="badge
                                @if ($categoryGrade === 'Sangat Baik') badge-excellent
                                @elseif($categoryGrade === 'Baik') badge-good
                                @elseif($categoryGrade === 'Cukup') badge-fair
                                @else badge-poor @endif"
                                style="margin-left: 10px;">
                                {{ $categoryGrade }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div style="text-align: center; padding: 50px; color: #666;">
            <h3>Belum Ada Data Assessment</h3>
            <p>Tidak ada skor penilaian yang ditemukan untuk sekolah dan periode ini.</p>
        </div>
    @endif

    <div class="footer">
        <p><strong>Laporan Assessment - SIMAK-PM</strong></p>
        <p>Digenerate pada: {{ $generatedAt }}</p>
        <p style="margin-top: 10px; font-size: 9px;">
            Dokumen ini dibuat secara otomatis oleh Sistem Informasi Model Assessment Kinerja Pendidikan Menengah
            (SIMAK-PM)
        </p>
    </div>
</body>

</html>
