<x-filament-panels::page>
    <div class="fi-page-content space-y-6">
        <!-- Form Filter -->
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5">
            {{ $this->form }}
        </div>

        <!-- Summary Statistics -->
        @php
            $stats = $this->getStats();
        @endphp

        @if ($stats['total'] > 0)
            <!-- Main Statistics Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                <!-- Total Penilaian -->
                <div class="fi-stats-card relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">
                    <div class="flex items-center gap-x-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary-100">
                            <x-heroicon-o-chart-bar class="h-6 w-6 text-primary-600" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-500">Total Penilaian</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total']) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Rata-rata Skor -->
                <div class="fi-stats-card relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">
                    <div class="flex items-center gap-x-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-success-100">
                            <x-heroicon-o-star class="h-6 w-6 text-success-600" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-500">Rata-rata Skor</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['average'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Skor Tertinggi -->
                <div class="fi-stats-card relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">
                    <div class="flex items-center gap-x-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-warning-100">
                            <x-heroicon-o-trophy class="h-6 w-6 text-warning-600" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-500">Skor Tertinggi</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['highest'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Skor Terendah -->
                <div class="fi-stats-card relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">
                    <div class="flex items-center gap-x-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-danger-100">
                            <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-danger-600" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-500">Skor Terendah</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['lowest'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Score Distribution -->
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5">
                <div class="mb-6">
                    <div class="flex items-center gap-x-3">
                        <x-heroicon-o-chart-pie class="h-6 w-6 text-primary-600" />
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Distribusi Skor Penilaian</h3>
                            <p class="text-sm text-gray-500">Breakdown skor berdasarkan kategori penilaian</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
                    <!-- Sangat Baik (4) -->
                    <div class="rounded-lg border border-success-200 bg-success-50 p-4 text-center transition-colors hover:bg-success-100">
                        <div class="mb-2 text-2xl font-bold text-success-700">{{ $stats['excellent'] }}</div>
                        <div class="mb-2 text-sm font-medium text-success-700">Sangat Baik (4)</div>
                        <div class="rounded-full bg-success-100 px-2 py-1 text-xs text-success-600">
                            {{ $stats['total'] > 0 ? round(($stats['excellent'] / $stats['total']) * 100, 1) : 0 }}%
                        </div>
                    </div>

                    <!-- Baik (3) -->
                    <div class="rounded-lg border border-info-200 bg-info-50 p-4 text-center transition-colors hover:bg-info-100">
                        <div class="mb-2 text-2xl font-bold text-info-700">{{ $stats['good'] }}</div>
                        <div class="mb-2 text-sm font-medium text-info-700">Baik (3)</div>
                        <div class="rounded-full bg-info-100 px-2 py-1 text-xs text-info-600">
                            {{ $stats['total'] > 0 ? round(($stats['good'] / $stats['total']) * 100, 1) : 0 }}%
                        </div>
                    </div>

                    <!-- Cukup (2) -->
                    <div class="rounded-lg border border-warning-200 bg-warning-50 p-4 text-center transition-colors hover:bg-warning-100">
                        <div class="mb-2 text-2xl font-bold text-warning-700">{{ $stats['fair'] }}</div>
                        <div class="mb-2 text-sm font-medium text-warning-700">Cukup (2)</div>
                        <div class="rounded-full bg-warning-100 px-2 py-1 text-xs text-warning-600">
                            {{ $stats['total'] > 0 ? round(($stats['fair'] / $stats['total']) * 100, 1) : 0 }}%
                        </div>
                    </div>

                    <!-- Kurang (1) -->
                    <div class="rounded-lg border border-danger-200 bg-danger-50 p-4 text-center transition-colors hover:bg-danger-100">
                        <div class="mb-2 text-2xl font-bold text-danger-700">{{ $stats['poor'] }}</div>
                        <div class="mb-2 text-sm font-medium text-danger-700">Kurang (1)</div>
                        <div class="rounded-full bg-danger-100 px-2 py-1 text-xs text-danger-600">
                            {{ $stats['total'] > 0 ? round(($stats['poor'] / $stats['total']) * 100, 1) : 0 }}%
                        </div>
                    </div>

                    <!-- Tidak Ada (0) -->
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-center transition-colors hover:bg-gray-100">
                        <div class="mb-2 text-2xl font-bold text-gray-700">{{ $stats['none'] }}</div>
                        <div class="mb-2 text-sm font-medium text-gray-700">Tidak Ada (0)</div>
                        <div class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600">
                            {{ $stats['total'] > 0 ? round(($stats['none'] / $stats['total']) * 100, 1) : 0 }}%
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="fi-section rounded-xl bg-white p-12 text-center shadow-sm ring-1 ring-gray-950/5">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gray-100">
                    <x-heroicon-o-document-magnifying-glass class="h-10 w-10 text-gray-400" />
                </div>
                <h3 class="mt-6 text-base font-semibold text-gray-900">Belum Ada Data Penilaian</h3>
                <p class="mx-auto mt-2 max-w-sm text-sm text-gray-500">
                    Pilih filter di atas untuk melihat data penilaian atau tunggu hingga ada data penilaian yang tersedia.
                </p>
            </div>
        @endif

        <!-- Data Table -->
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
