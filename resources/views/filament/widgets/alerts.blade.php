<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            {{-- Header --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-bell class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        System Alerts
                    </h3>
                    @if ($totalAlerts > 0)
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $hasUrgent
                                ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                : ($hasWarning
                                    ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                    : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200') }}">
                            {{ $totalAlerts }}
                        </span>
                    @endif
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Last updated: {{ now()->format('H:i') }}
                </div>
            </div>

            {{-- Alerts List --}}
            @if ($alerts->isNotEmpty())
                <div class="space-y-3">
                    @foreach ($alerts as $alert)
                        <div
                            class="flex items-start gap-3 p-3 rounded-lg border transition-all duration-200 hover:shadow-sm
                            @switch($alert['type'])
                                @case('urgent')
                                    bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800
                                    @break
                                @case('warning')
                                    bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800
                                    @break
                                @case('success')
                                    bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800
                                    @break
                                @default
                                    bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800
                            @endswitch">

                            {{-- Icon --}}
                            <div class="flex-shrink-0 mt-0.5">
                                @switch($alert['type'])
                                    @case('urgent')
                                        <x-dynamic-component :component="$alert['icon']" class="h-5 w-5 text-red-500 dark:text-red-400" />
                                    @break

                                    @case('warning')
                                        <x-dynamic-component :component="$alert['icon']"
                                            class="h-5 w-5 text-yellow-500 dark:text-yellow-400" />
                                    @break

                                    @case('success')
                                        <x-dynamic-component :component="$alert['icon']"
                                            class="h-5 w-5 text-green-500 dark:text-green-400" />
                                    @break

                                    @default
                                        <x-dynamic-component :component="$alert['icon']"
                                            class="h-5 w-5 text-blue-500 dark:text-blue-400" />
                                @endswitch
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900 dark:text-white text-sm">
                                            {{ $alert['title'] }}
                                        </h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                            {{ $alert['message'] }}
                                        </p>
                                    </div>

                                    {{-- Action Button --}}
                                    @if (isset($alert['action']) && isset($alert['url']))
                                        <a href="{{ $alert['url'] }}"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md transition-colors duration-200
                                               @switch($alert['type'])
                                                   @case('urgent')
                                                       text-red-700 bg-red-100 hover:bg-red-200 dark:text-red-200 dark:bg-red-800 dark:hover:bg-red-700
                                                       @break
                                                   @case('warning')
                                                       text-yellow-700 bg-yellow-100 hover:bg-yellow-200 dark:text-yellow-200 dark:bg-yellow-800 dark:hover:bg-yellow-700
                                                       @break
                                                   @case('success')
                                                       text-green-700 bg-green-100 hover:bg-green-200 dark:text-green-200 dark:bg-green-800 dark:hover:bg-green-700
                                                       @break
                                                   @default
                                                       text-blue-700 bg-blue-100 hover:bg-blue-200 dark:text-blue-200 dark:bg-blue-800 dark:hover:bg-blue-700
                                               @endswitch">
                                            {{ $alert['action'] }}
                                            <x-heroicon-m-arrow-right class="ml-1 h-3 w-3" />
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- No Alerts State --}}
                <div class="text-center py-8">
                    <div class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500">
                        <x-heroicon-o-check-circle class="h-12 w-12" />
                    </div>
                    <h3 class="mt-4 text-sm font-medium text-gray-900 dark:text-white">All Clear!</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        No urgent alerts at the moment. Keep up the good work!
                    </p>
                </div>
            @endif

            {{-- Footer Actions --}}
            @if ($alerts->isNotEmpty())
                <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Showing {{ $alerts->count() }} most important alerts
                    </p>
                    <a href="{{ route('filament.admin.resources.school-assessments.index') }}"
                        class="text-xs text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                        View All Assessments →
                    </a>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
