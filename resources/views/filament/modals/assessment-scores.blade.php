<div class="space-y-6">
    {{-- Header Information --}}
    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Sekolah</h3>
                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $schoolAssessment->school->nama_sekolah }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Periode</h3>
                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $schoolAssessment->period->nama_periode }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Asesor</h3>
                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $schoolAssessment->assessor->name }}</p>
            </div>
        </div>
    </div>

    @if ($assessmentScores->isNotEmpty())
        {{-- Overall Summary (Moved to Top) --}}
        <div
            class="bg-primary-50 dark:bg-primary-900/20 rounded-lg p-6 border-2 border-primary-200 dark:border-primary-700">
            @php
                $allScores = $assessmentScores->flatten();
                $totalScore = $allScores->sum('skor');
                $averageScore = $allScores->avg('skor');
                $overallGrade = match (true) {
                    $averageScore >= 3.5 => 'Sangat Baik',
                    $averageScore >= 2.5 => 'Baik',
                    $averageScore >= 1.5 => 'Cukup',
                    default => 'Kurang',
                };
                $overallGradeColor = match ($overallGrade) {
                    'Sangat Baik' => 'text-green-600 dark:text-green-400',
                    'Baik' => 'text-blue-600 dark:text-blue-400',
                    'Cukup' => 'text-yellow-600 dark:text-yellow-400',
                    'Kurang' => 'text-red-600 dark:text-red-400',
                    default => 'text-gray-600 dark:text-gray-400',
                };
            @endphp
            <div class="text-center">
                <h2 class="text-xl font-bold text-primary-900 dark:text-primary-100 mb-4">
                    🏆 Ringkasan Penilaian Keseluruhan
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                        <p class="text-sm font-medium text-primary-700 dark:text-primary-300">Total Skor</p>
                        <p class="text-3xl font-bold text-primary-900 dark:text-primary-100 mt-1">
                            {{ number_format($totalScore, 2) }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                        <p class="text-sm font-medium text-primary-700 dark:text-primary-300">Skor Rata-rata</p>
                        <p class="text-3xl font-bold text-primary-900 dark:text-primary-100 mt-1">
                            {{ number_format($averageScore, 2) }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                        <p class="text-sm font-medium text-primary-700 dark:text-primary-300">Nilai Keseluruhan</p>
                        <p class="text-4xl font-bold {{ $overallGradeColor }} mt-1">
                            {{ $overallGrade }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        {{-- <div class="flex flex-wrap gap-3 justify-center">
            <button onclick="shareReport('{{ $schoolAssessment->school->nama_sekolah }}')"
                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z">
                    </path>
                </svg>
                Share Report
            </button>
            <button onclick="printReport()"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                    </path>
                </svg>
                Print
            </button>
        </div> --}}

        {{-- Assessment Scores by Category --}}
        <div class="space-y-6">
            @foreach ($assessmentScores as $categoryName => $scores)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    {{-- Category Header --}}
                    <div
                        class="bg-primary-50 dark:bg-primary-900/20 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-primary-900 dark:text-primary-100">
                            {{ $categoryName }}
                        </h3>
                        <p class="text-sm text-primary-700 dark:text-primary-300 mt-1">
                            {{ $scores->count() }} indikator penilaian
                        </p>
                    </div>

                    {{-- Indicators Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        Indikator
                                    </th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider w-24">
                                        Skor
                                    </th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider w-24">
                                        Nilai
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                        Catatan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($scores as $score)
                                    <tr
                                        class="hover:bg-gray-50 hover:shadow-sm dark:hover:bg-gray-800 transition-all duration-200 group cursor-pointer">
                                        <td class="px-4 py-3">
                                            <div>
                                                <p
                                                    class="text-sm font-medium text-gray-500 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors duration-200">
                                                    {{ $score->assessmentIndicator->nama_indikator }}
                                                </p>
                                                @if ($score->assessmentIndicator->deskripsi)
                                                    <p
                                                        class="text-xs text-gray-500 dark:text-gray-400 mt-1 group-hover:text-gray-800 dark:group-hover:text-gray-100 transition-colors duration-200">
                                                        {{ $score->assessmentIndicator->deskripsi }}
                                                    </p>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <p
                                                class="text-bold text-xs text-gray-500 dark:text-gray-400 mt-1 group-hover:text-gray-800 dark:group-hover:text-gray-100 transition-colors duration-200">

                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold transition-all duration-200 group-hover:shadow-md
                                                @if ($score->skor >= 3.5) bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100 group-hover:bg-green-200 dark:group-hover:bg-green-600
                                                @elseif($score->skor >= 2.5) bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100 group-hover:bg-blue-200 dark:group-hover:bg-blue-600
                                                @elseif($score->skor >= 1.5) bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-600
                                                @else bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100 group-hover:bg-red-200 dark:group-hover:bg-red-600 @endif">
                                                    {{ number_format($score->skor, 2) }}
                                                </span>
                                            </p>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <p
                                                class="text-bold text-xs text-gray-500 dark:text-gray-400 mt-1 group-hover:text-gray-800 dark:group-hover:text-gray-100 transition-colors duration-200">
                                                @php
                                                    $gradeDisplay = match ($score->grade) {
                                                        'Sangat Baik', 'A' => 'Sangat Baik',
                                                        'Baik', 'B' => 'Baik',
                                                        'Cukup', 'C' => 'Cukup',
                                                        'Kurang', 'D' => 'Kurang',
                                                        default => $score->grade,
                                                    };
                                                    $gradeColor = match ($score->grade) {
                                                        'Sangat Baik',
                                                        'A'
                                                            => 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100 group-hover:bg-green-200 dark:group-hover:bg-green-600',
                                                        'Baik',
                                                        'B'
                                                            => 'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100 group-hover:bg-blue-200 dark:group-hover:bg-blue-600',
                                                        'Cukup',
                                                        'C'
                                                            => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100 group-hover:bg-yellow-200 dark:group-hover:bg-yellow-600',
                                                        'Kurang',
                                                        'D'
                                                            => 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100 group-hover:bg-red-200 dark:group-hover:bg-red-600',
                                                        default
                                                            => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100 group-hover:bg-gray-200 dark:group-hover:bg-gray-600',
                                                    };
                                                @endphp
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold transition-all duration-200 group-hover:shadow-md {{ $gradeColor }}">
                                                    {{ $gradeDisplay }}
                                                </span>

                                            </p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <p {{-- class="text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-700 dark:group-hover:text-white transition-colors duration-200 group-hover:font-semibold"> --}}
                                                class="text-xs text-gray-500 dark:text-gray-400 mt-1 group-hover:text-gray-800 dark:group-hover:text-gray-100 transition-colors duration-200">
                                                {{ $score->catatan ?: '-' }}
                                            </p>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Category Summary --}}
                    <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">
                                Total Skor Kategori:
                            </span>
                            <div class="flex items-center space-x-2">
                                @php
                                    $categoryTotal = $scores->sum('skor');
                                    $categoryAverage = $scores->avg('skor');

                                    // Get category weight from the first score's indicator's category
                                    $firstScore = $scores->first();
                                    $categoryWeight =
                                        $firstScore &&
                                        $firstScore->assessmentIndicator &&
                                        $firstScore->assessmentIndicator->category
                                            ? $firstScore->assessmentIndicator->category->bobot_penilaian
                                            : 0;

                                    // Calculate weighted category score
                                    $weightedCategoryScore = $categoryAverage * ($categoryWeight / 100);

                                    $categoryGrade = match (true) {
                                        $categoryAverage >= 3.5 => 'Sangat Baik',
                                        $categoryAverage >= 2.5 => 'Baik',
                                        $categoryAverage >= 1.5 => 'Cukup',
                                        default => 'Kurang',
                                    };
                                    $categoryGradeColor = match ($categoryGrade) {
                                        'Sangat Baik'
                                            => 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100',
                                        'Baik' => 'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100',
                                        'Cukup'
                                            => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100',
                                        'Kurang' => 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100',
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100',
                                    };
                                @endphp
                                <div class="text-right">
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                        {{ number_format($categoryTotal, 2) }} (Rata-rata:
                                        {{ number_format($categoryAverage, 2) }})
                                    </span>
                                    <br>
                                    <span class="text-xs text-gray-600 dark:text-gray-400">
                                        Bobot: {{ number_format($categoryWeight, 1) }}% |
                                        Skor Berbobot: {{ number_format($weightedCategoryScore, 2) }}
                                    </span>
                                </div>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $categoryGradeColor }}">
                                    {{ $categoryGrade }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8">
            <div class="mx-auto h-16 w-16 text-gray-400">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Belum Ada Data Assessment</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Tidak ada skor penilaian yang ditemukan untuk sekolah dan periode ini.
            </p>
        </div>
    @endif
</div>

<script>
    function shareReport(schoolName) {
        const url = window.location.href;
        const shareText = `Assessment Report - ${schoolName}\n${url}`;

        if (navigator.share) {
            // Use Web Share API if available (mobile devices)
            navigator.share({
                title: `Assessment Report - ${schoolName}`,
                text: `Laporan penilaian assessment untuk ${schoolName}`,
                url: url
            }).catch(err => {
                console.log('Share cancelled or failed:', err);
            });
        } else if (navigator.clipboard) {
            // Copy to clipboard
            navigator.clipboard.writeText(shareText).then(() => {
                alert('Link laporan telah disalin ke clipboard!');
            }).catch(err => {
                console.error('Failed to copy to clipboard:', err);
                // Fallback to prompt
                prompt('Copy link ini untuk berbagi:', url);
            });
        } else {
            // Final fallback
            prompt('Copy link ini untuk berbagi:', url);
        }
    }

    function printReport() {
        window.print();
    }
</script>
</script>
