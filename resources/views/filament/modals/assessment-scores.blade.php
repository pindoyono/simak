<div class="space-y-6">
    {{-- Header Information --}}
    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Sekolah</h3>
                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $schoolAssessment->school->nama_sekolah }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Periode</h3>
                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $schoolAssessment->period->nama_periode }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Asesor</h3>
                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $schoolAssessment->assessor->name }}</p>
            </div>
        </div>
    </div>

    {{-- Assessment Scores by Category --}}
    @if($assessmentScores->isNotEmpty())
        <div class="space-y-6">
            @foreach($assessmentScores as $categoryName => $scores)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    {{-- Category Header --}}
                    <div class="bg-primary-50 dark:bg-primary-900/20 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
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
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Indikator
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">
                                        Skor
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">
                                        Grade
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Catatan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($scores as $score)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-4 py-3">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $score->assessmentIndicator->nama_indikator }}
                                                </p>
                                                @if($score->assessmentIndicator->deskripsi)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        {{ $score->assessmentIndicator->deskripsi }}
                                                    </p>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($score->skor >= 3.5) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @elseif($score->skor >= 2.5) bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                @elseif($score->skor >= 1.5) bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                @endif">
                                                {{ number_format($score->skor, 2) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($score->grade === 'A') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @elseif($score->grade === 'B') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                @elseif($score->grade === 'C') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                @endif">
                                                {{ $score->grade }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="text-sm text-gray-900 dark:text-white">
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
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Total Skor Kategori:
                            </span>
                            <div class="flex items-center space-x-2">
                                @php
                                    $categoryTotal = $scores->sum('skor');
                                    $categoryAverage = $scores->avg('skor');
                                    $categoryGrade = $categoryAverage >= 3.5 ? 'A' : ($categoryAverage >= 2.5 ? 'B' : ($categoryAverage >= 1.5 ? 'C' : 'D'));
                                @endphp
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($categoryTotal, 2) }} (Rata-rata: {{ number_format($categoryAverage, 2) }})
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($categoryGrade === 'A') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($categoryGrade === 'B') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                    @elseif($categoryGrade === 'C') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @endif">
                                    Grade {{ $categoryGrade }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Overall Summary --}}
        <div class="bg-primary-50 dark:bg-primary-900/20 rounded-lg p-4 mt-6">
            @php
                $allScores = $assessmentScores->flatten();
                $totalScore = $allScores->sum('skor');
                $averageScore = $allScores->avg('skor');
                $overallGrade = $averageScore >= 3.5 ? 'A' : ($averageScore >= 2.5 ? 'B' : ($averageScore >= 1.5 ? 'C' : 'D'));
            @endphp
            <div class="text-center">
                <h3 class="text-lg font-semibold text-primary-900 dark:text-primary-100 mb-2">
                    Ringkasan Penilaian Keseluruhan
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-primary-700 dark:text-primary-300">Total Skor</p>
                        <p class="text-2xl font-bold text-primary-900 dark:text-primary-100">{{ number_format($totalScore, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-primary-700 dark:text-primary-300">Skor Rata-rata</p>
                        <p class="text-2xl font-bold text-primary-900 dark:text-primary-100">{{ number_format($averageScore, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-primary-700 dark:text-primary-300">Grade Keseluruhan</p>
                        <p class="text-3xl font-bold 
                            @if($overallGrade === 'A') text-green-600 dark:text-green-400
                            @elseif($overallGrade === 'B') text-blue-600 dark:text-blue-400
                            @elseif($overallGrade === 'C') text-yellow-600 dark:text-yellow-400
                            @else text-red-600 dark:text-red-400
                            @endif">
                            {{ $overallGrade }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-8">
            <div class="mx-auto h-16 w-16 text-gray-400">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Belum Ada Data Assessment</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Tidak ada skor penilaian yang ditemukan untuk sekolah dan periode ini.
            </p>
        </div>
    @endif
</div>
