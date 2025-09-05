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

        /* Accordion styling */
        .accordion-header {
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        .accordion-header:hover {
            background-color: rgb(249 250 251);
        }

        .dark .accordion-header:hover {
            background-color: rgb(31 41 55);
        }

        .accordion-icon {
            transition: transform 0.2s ease-in-out;
        }

        .accordion-content {
            transition: all 0.3s ease-in-out;
            overflow: hidden;
        }
    </style>

    <div class="space-y-6" x-data="{
        filterOpen: true,
        dataOpen: true
    }">
        {{-- Filter Form Section --}}
        <div
            class="rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 border border-gray-200 dark:border-gray-700">
            <!-- Accordion Header -->
            <div class="accordion-header border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-6 py-4 rounded-t-xl"
                @click="filterOpen = !filterOpen">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-funnel class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Filter Data Penilaian</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Klik untuk expand/collapse</span>
                        <x-heroicon-o-chevron-down class="h-5 w-5 text-gray-500 dark:text-gray-400 accordion-icon transition-transform duration-200"
                            x-bind:class="{ 'transform rotate-180': filterOpen }" />
                    </div>
                </div>
            </div>

            <!-- Accordion Content -->
            <div class="accordion-content overflow-hidden" x-show="filterOpen"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 max-h-0"
                x-transition:enter-end="opacity-100 max-h-screen" x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 max-h-screen" x-transition:leave-end="opacity-0 max-h-0">
                <div class="p-6 bg-white dark:bg-gray-900">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Gunakan filter di bawah untuk menyaring
                        data
                        penilaian berdasarkan kriteria tertentu</p>
                    <div class="dark:text-white">
                        {{ $this->form }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Table Section --}}
        <div
            class="rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 border border-gray-200 dark:border-gray-700">
            <!-- Accordion Header -->
            <div class="accordion-header border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-6 py-4 rounded-t-xl"
                @click="dataOpen = !dataOpen">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-table-cells class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Data Penilaian Detail</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Klik untuk expand/collapse</span>
                        <x-heroicon-o-chevron-down class="h-5 w-5 text-gray-500 dark:text-gray-400 accordion-icon transition-transform duration-200"
                            x-bind:class="{ 'transform rotate-180': dataOpen }" />
                    </div>
                </div>
            </div>

            <!-- Accordion Content -->
            <div class="accordion-content overflow-hidden" x-show="dataOpen"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 max-h-0"
                x-transition:enter-end="opacity-100 max-h-screen" x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 max-h-screen" x-transition:leave-end="opacity-0 max-h-0">
                <div class="p-6 bg-white dark:bg-gray-900">
                    {{ $this->table }}
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
