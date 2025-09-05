<div class="space-y-6">
    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
        <div class="flex items-center space-x-2">
            <x-heroicon-s-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Informasi Kriteria</h3>
        </div>
        <p class="mt-2 text-sm text-blue-700 dark:text-blue-300">
            Berikut adalah kriteria penilaian untuk setiap indikator. Gunakan sebagai acuan dalam memberikan skor.
        </p>
    </div>

    @foreach ($categories as $category)
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $category->nama_kategori }}
                </h2>
                @if ($category->deskripsi_kategori)
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ $category->deskripsi_kategori }}
                    </p>
                @endif
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($category->indicators as $indicator)
                    <div class="p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">
                                    {{ $indicator->nama_indikator }}
                                </h3>
                                @if ($indicator->deskripsi_indikator)
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $indicator->deskripsi_indikator }}
                                    </p>
                                @endif
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    Max: {{ $indicator->skor_maksimal ?? 4 }}
                                </span>
                                @if ($indicator->bobot_indikator && $indicator->bobot_indikator != 1)
                                    <span
                                        class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Bobot: {{ $indicator->bobot_indikator }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if ($indicator->kriteria_penilaian)
                            <div class="mt-3">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                                    Kriteria Penilaian:
                                </h4>

                                @php
                                    $criteria = $indicator->kriteria_penilaian;
                                    $isStructured = preg_match('/\d+\s*=\s*[^,]+/', $criteria);
                                @endphp

                                @if ($isStructured)
                                    {{-- Structured criteria with scores --}}
                                    <div class="space-y-2">
                                        @php
                                            preg_match_all(
                                                '/(\d+)\s*=\s*([^,]+)/',
                                                $criteria,
                                                $matches,
                                                PREG_SET_ORDER,
                                            );
                                        @endphp
                                        @foreach ($matches as $match)
                                            @php
                                                $score = (int) $match[1];
                                                $description = trim($match[2]);
                                            @endphp
                                            <div
                                                class="flex items-start space-x-3 p-2 bg-gray-50 dark:bg-gray-800 rounded">
                                                <span
                                                    class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full flex items-center justify-center text-sm font-medium">
                                                    {{ $score }}
                                                </span>
                                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                                    {{ $description }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{-- Unstructured criteria --}}
                                    <div
                                        class="p-3 bg-gray-50 dark:bg-gray-800 rounded text-sm text-gray-700 dark:text-gray-300">
                                        {{ $criteria }}
                                    </div>
                                @endif
                            </div>
                        @else
                            {{-- Default criteria if none specified --}}
                            <div class="mt-3">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                                    Kriteria Penilaian (Default):
                                </h4>
                                <div class="space-y-2">
                                    @php
                                        $maxScore = $indicator->skor_maksimal ?? 4;
                                        $defaultLabels = [
                                            1 => 'Sangat Kurang - Tidak memenuhi standar minimal',
                                            2 => 'Kurang - Memenuhi sebagian kecil standar',
                                            3 => 'Cukup - Memenuhi standar dasar',
                                            4 => 'Baik - Memenuhi standar dengan baik',
                                            5 => 'Sangat Baik - Melebihi standar yang ditetapkan',
                                        ];
                                    @endphp
                                    @for ($i = 1; $i <= $maxScore; $i++)
                                        <div class="flex items-start space-x-3 p-2 bg-gray-50 dark:bg-gray-800 rounded">
                                            <span
                                                class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full flex items-center justify-center text-sm font-medium">
                                                {{ $i }}
                                            </span>
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ $defaultLabels[$i] ?? "Level penilaian $i" }}
                                            </span>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @if ($categories->isEmpty())
        <div class="text-center py-8">
            <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada kriteria</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Belum ada kategori dan indikator penilaian yang tersedia.
            </p>
        </div>
    @endif
</div>
