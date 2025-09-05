<x-filament-panels::page>
    <style>
        /* Ensure dark mode compatibility */
        .dark .fi-section,
        .dark .fi-section-content,
        .dark .fi-section-header,
        .dark .fi-fo-section {
            background-color: rgb(17 24 39) !important;
            color: rgb(243 244 246) !important;
        }
        
        .dark .fi-input-wrp {
            background-color: rgb(31 41 55) !important;
        }
        
        .dark .fi-select-input {
            background-color: rgb(31 41 55) !important;
            color: rgb(243 244 246) !important;
        }

        .dark .fi-ta-content {
            background-color: rgb(17 24 39) !important;
        }
    </style>
    
    <div class="space-y-6">
        {{-- Filter Form Section --}}
        <div class="rounded-xl bg-white dark:bg-gray-900 p-6 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Filter Data Penilaian</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Gunakan filter di bawah untuk menyaring data penilaian berdasarkan kriteria tertentu</p>
            
            <div class="dark:text-white">
                {{ $this->form }}
            </div>
        </div>

        {{-- Data Table Section --}}
        <div class="rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 border border-gray-200 dark:border-gray-700">
            <div class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-6 py-4 rounded-t-xl">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-table-cells class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Data Penilaian Detail</h3>
                </div>
            </div>
            <div class="p-6 bg-white dark:bg-gray-900">
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
