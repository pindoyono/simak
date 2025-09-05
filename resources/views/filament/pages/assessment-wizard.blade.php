<x-filament-panels::page>
    <style>
        /* Force all radio buttons horizontal - no scroll */
        .fi-fo-radio .fi-fo-field-wrp-label+div,
        .fi-fo-radio fieldset,
        .fi-fo-radio [role="radiogroup"],
        .fi-fo-radio div[class*="grid"],
        .fi-fo-radio .grid,
        .fi-fo-radio>div>div,
        .radio-horizontal,
        [data-field-wrapper] .fi-fo-radio div {
            display: flex !important;
            flex-direction: row !important;
            gap: 0.5rem !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            width: 100% !important;
            overflow: visible !important;
        }

        .fi-fo-radio .fi-fo-field-wrp-label+div>div,
        .fi-fo-radio fieldset>div,
        .fi-fo-radio [role="radiogroup"]>div,
        .radio-horizontal>div {
            flex: 1 1 0 !important;
            min-width: 0 !important;
            white-space: nowrap !important;
            margin: 0 !important;
            display: inline-flex !important;
            font-size: 0.8rem !important;
        }

        /* Make radio button labels smaller and fit better */
        .fi-fo-radio label,
        .radio-horizontal label {
            font-size: 0.8rem !important;
            padding: 0.25rem 0.5rem !important;
            text-overflow: ellipsis !important;
            overflow: hidden !important;
            white-space: nowrap !important;
        }

        /* Override any grid classes that might stack items */
        .fi-fo-radio .grid-cols-1,
        .fi-fo-radio .grid-cols-2,
        .fi-fo-radio .grid-cols-3,
        .fi-fo-radio .grid-cols-4 {
            display: flex !important;
            grid-template-columns: none !important;
        }

        /* Compact spacing */
        .fi-section-content-ctn {
            padding: 1rem !important;
        }

        .fi-section-header {
            padding: 0.75rem 1rem !important;
        }

        .fi-fo-field-wrp {
            margin-bottom: 0.75rem !important;
        }

        /* Remove scroll - make container fit content */
        .fi-fo-radio {
            width: 100% !important;
            overflow: visible !important;
        }
    </style>

    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                            Wizard Penilaian Sekolah
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Sistem penilaian sekolah dalam 3 langkah mudah
                        </p>
                    </div>
                    <x-heroicon-o-academic-cap class="h-8 w-8 text-primary-600" />
                </div>

                {{ $this->form }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
