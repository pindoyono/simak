<x-filament-panels::page>
    <!-- Page Header -->
    <x-filament::section>
        <x-slot name="heading">
            Assessment Wizard
        </x-slot>
        <x-slot name="description">
            Complete your assessment in {{ count($this->getSteps()) }} easy steps. Step {{ $this->currentStep }} of
            {{ count($this->getSteps()) }}.
        </x-slot>

        <!-- Progress Steps -->
        <div class="mt-6">
            <div class="flex items-center justify-between">
                @foreach ($this->getSteps() as $step => $label)
                    <div class="flex items-center {{ $step < count($this->getSteps()) ? 'flex-1' : '' }}">
                        <div class="flex items-center">
                            <div @class([
                                'flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium',
                                'bg-primary-600 text-white' => $this->currentStep === $step,
                                'bg-green-600 text-white' => $this->currentStep > $step,
                                'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300' =>
                                    $this->currentStep < $step,
                            ])>
                                @if ($this->currentStep > $step)
                                    <x-heroicon-m-check class="w-4 h-4" />
                                @else
                                    {{ $step }}
                                @endif
                            </div>
                            <div class="ml-3">
                                <p @class([
                                    'text-sm font-medium',
                                    'text-primary-600 dark:text-primary-400' => $this->currentStep === $step,
                                    'text-green-600 dark:text-green-400' => $this->currentStep > $step,
                                    'text-gray-500 dark:text-gray-400' => $this->currentStep < $step,
                                ])>
                                    {{ $label }}
                                </p>
                            </div>
                        </div>
                        @if ($step < count($this->getSteps()))
                            <div class="flex-1 mx-4">
                                <div @class([
                                    'h-0.5 w-full',
                                    'bg-green-600' => $this->currentStep > $step,
                                    'bg-gray-200 dark:bg-gray-700' => $this->currentStep <= $step,
                                ])></div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </x-filament::section>

    <!-- Form Content -->
    <x-filament::section>
        <x-slot name="heading">
            {{ $this->getCurrentStepLabel() }}
        </x-slot>

        <form wire:submit="submit">
            {{ $this->form }}
        </form>
    </x-filament::section>

    <!-- Navigation -->
    <x-filament::section>
        <div class="flex justify-between items-center">
            <div>
                @if ($this->currentStep > 1)
                    <x-filament::button wire:click="previousStep" color="gray" icon="heroicon-m-arrow-left"
                        icon-position="before">
                        Previous
                    </x-filament::button>
                @endif
            </div>

            <div class="flex items-center space-x-3 text-sm text-gray-500 dark:text-gray-400">
                <span>Progress:</span>
                <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-primary-600 h-2 rounded-full transition-all duration-300"
                        style="width: {{ ($this->currentStep / count($this->getSteps())) * 100 }}%"></div>
                </div>
                <span>{{ round(($this->currentStep / count($this->getSteps())) * 100) }}%</span>
            </div>

            <div>
                @if ($this->currentStep < count($this->getSteps()))
                    <x-filament::button wire:click="nextStep" icon="heroicon-m-arrow-right" icon-position="after">
                        Next
                    </x-filament::button>
                @else
                    <x-filament::button type="submit" wire:click="submit" color="success" icon="heroicon-m-check"
                        icon-position="before">
                        Submit Assessment
                    </x-filament::button>
                @endif
            </div>
        </div>
    </x-filament::section>

    <!-- Loading State -->
    <div wire:loading.delay class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm">
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-6 mx-4 max-w-sm w-full">
            <div class="flex flex-col items-center space-y-4">
                <x-filament::loading-indicator class="h-8 w-8 text-primary-600" />
                <div class="text-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Processing Assessment</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Please wait while we save your data...
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
