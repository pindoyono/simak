<x-filament-panels::page @class(['fi-assessment-wizard-page', 'fi-simple-page' => true])>
    <div class="space-y-6">
        <!-- Progress Steps -->
        <div
            class="bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-800 dark:via-gray-900 dark:to-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                @foreach ($this->getSteps() as $step => $label)
                    <div class="flex items-center {{ $step < count($this->getSteps()) ? 'flex-1' : '' }}">
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 w-10 h-10 rounded-full border-2 transition-all duration-300 {{ $this->currentStep === $step ? 'border-primary-500 bg-primary-500 text-white shadow-lg scale-110' : ($this->currentStep > $step ? 'border-green-500 bg-green-500 text-white shadow-md' : 'border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 text-gray-500 dark:text-gray-400') }} flex items-center justify-center text-sm font-bold">
                                @if ($this->currentStep > $step)
                                    <x-heroicon-s-check class="w-5 h-5" />
                                @else
                                    {{ $step }}
                                @endif
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-semibold transition-colors duration-200 {{ $this->currentStep === $step ? 'text-primary-600 dark:text-primary-400' : ($this->currentStep > $step ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400') }}">
                                    {{ $label }}
                                </p>
                            </div>
                        </div>
                        @if ($step < count($this->getSteps()))
                            <div class="flex-1 mx-4">
                                <div
                                    class="h-1 rounded-full transition-all duration-500 {{ $this->currentStep > $step ? 'bg-gradient-to-r from-green-400 to-green-600' : 'bg-gray-200 dark:bg-gray-600' }}">
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Current Step Content -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div
                class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <div
                            class="w-8 h-8 bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 rounded-lg flex items-center justify-center">
                            <span class="text-sm font-bold">{{ $this->currentStep }}</span>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $this->getCurrentStepLabel() }}
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Step {{ $this->currentStep }} of {{ count($this->getSteps()) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-gray-800">
                <form wire:submit="submit">
                    {{ $this->form }}
                </form>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div
            class="flex justify-between items-center bg-gray-50 dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <div>
                @if ($this->currentStep > 1)
                    <x-filament::button wire:click="previousStep" color="gray" outlined size="lg"
                        class="transition-all duration-200 hover:scale-105">
                        <x-heroicon-s-arrow-left class="w-4 h-4 mr-2" />
                        Previous
                    </x-filament::button>
                @endif
            </div>

            <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <span>Progress:</span>
                <div class="w-32 bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                    <div class="bg-gradient-to-r from-primary-400 to-primary-600 h-2 rounded-full transition-all duration-500"
                        style="width: {{ ($this->currentStep / count($this->getSteps())) * 100 }}%"></div>
                </div>
                <span>{{ round(($this->currentStep / count($this->getSteps())) * 100) }}%</span>
            </div>

            <div class="flex space-x-3">
                @if ($this->currentStep < 4)
                    <x-filament::button wire:click="nextStep" color="primary" size="lg"
                        class="transition-all duration-200 hover:scale-105 shadow-md">
                        Next
                        <x-heroicon-s-arrow-right class="w-4 h-4 ml-2" />
                    </x-filament::button>
                @else
                    <x-filament::button type="submit" wire:click="submit" color="success" size="lg"
                        class="transition-all duration-200 hover:scale-105 shadow-md">
                        <x-heroicon-s-check class="w-4 h-4 mr-2" />
                        Submit Assessment
                    </x-filament::button>
                @endif
            </div>
        </div>

        <!-- Loading State -->
        <div wire:loading.delay
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm">
            <div
                class="bg-white dark:bg-gray-800 rounded-xl p-8 shadow-2xl border border-gray-200 dark:border-gray-700 max-w-sm mx-4">
                <div class="flex flex-col items-center space-y-4">
                    <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary-200 border-t-primary-600">
                    </div>
                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Processing Assessment</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Please wait while we save your data...
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .fi-assessment-wizard-page {
            background: linear-gradient(135deg, #f6f8fb 0%, #e9ecef 100%);
            min-height: 100vh;
        }

        .dark .fi-assessment-wizard-page {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        }

        /* Custom scrollbar for better UX */
        .fi-assessment-wizard-page ::-webkit-scrollbar {
            width: 6px;
        }

        .fi-assessment-wizard-page ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .fi-assessment-wizard-page ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .fi-assessment-wizard-page ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Dark mode scrollbar */
        .dark .fi-assessment-wizard-page ::-webkit-scrollbar-track {
            background: #374151;
        }

        .dark .fi-assessment-wizard-page ::-webkit-scrollbar-thumb {
            background: #6b7280;
        }

        .dark .fi-assessment-wizard-page ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
</x-filament-panels::page>
