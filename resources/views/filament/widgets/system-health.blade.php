<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            {{-- Header --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-2">
                        <div
                            class="w-3 h-3 rounded-full 
                            @switch($status['status'])
                                @case('excellent')
                                    bg-green-500 animate-pulse
                                    @break
                                @case('warning')
                                    bg-yellow-500 animate-pulse
                                    @break
                                @case('critical')
                                    bg-red-500 animate-pulse
                                    @break
                                @default
                                    bg-gray-500
                            @endswitch">
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            System Health
                        </h3>
                    </div>
                    <span
                        class="px-2 py-1 rounded-full text-xs font-medium
                        @switch($status['status'])
                            @case('excellent')
                                bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @break
                            @case('warning')
                                bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @break
                            @case('critical')
                                bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                @break
                            @default
                                bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                        @endswitch">
                        {{ ucfirst($status['status']) }}
                    </span>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Auto-refresh: 30s
                </div>
            </div>

            {{-- Main Metrics Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- System Metrics --}}
                <div
                    class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-3 flex items-center gap-2">
                        <x-heroicon-o-server class="h-4 w-4" />
                        System Status
                    </h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-blue-700 dark:text-blue-300">Schools</span>
                            <span
                                class="text-sm font-semibold text-blue-900 dark:text-blue-100">{{ $metrics['schools'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-blue-700 dark:text-blue-300">Active Assessors</span>
                            <span
                                class="text-sm font-semibold text-blue-900 dark:text-blue-100">{{ $metrics['assessors'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-blue-700 dark:text-blue-300">Pending Reviews</span>
                            <span
                                class="text-sm font-semibold text-blue-900 dark:text-blue-100">{{ $metrics['pending'] }}</span>
                        </div>
                    </div>
                </div>

                {{-- Performance Metrics --}}
                <div
                    class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
                    <h4 class="font-semibold text-green-900 dark:text-green-100 mb-3 flex items-center gap-2">
                        <x-heroicon-o-bolt class="h-4 w-4" />
                        Performance
                    </h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-green-700 dark:text-green-300">Response Time</span>
                            <span
                                class="text-sm font-semibold text-green-900 dark:text-green-100">{{ $performance['response_time'] }}ms</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-green-700 dark:text-green-300">System Load</span>
                            <span
                                class="text-sm font-semibold text-green-900 dark:text-green-100">{{ $performance['system_load'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-green-700 dark:text-green-300">Data Freshness</span>
                            <span
                                class="text-sm font-semibold 
                                @if ($performance['data_freshness'] === 'Fresh') text-green-600 dark:text-green-400
                                @elseif($performance['data_freshness'] === 'Recent') text-blue-600 dark:text-blue-400
                                @elseif($performance['data_freshness'] === 'Moderate') text-yellow-600 dark:text-yellow-400
                                @else text-red-600 dark:text-red-400 @endif">
                                {{ $performance['data_freshness'] }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Quality Metrics --}}
                <div
                    class="bg-gradient-to-br from-purple-50 to-violet-50 dark:from-purple-900/20 dark:to-violet-900/20 rounded-lg p-4 border border-purple-200 dark:border-purple-800">
                    <h4 class="font-semibold text-purple-900 dark:text-purple-100 mb-3 flex items-center gap-2">
                        <x-heroicon-o-chart-bar class="h-4 w-4" />
                        Quality
                    </h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-purple-700 dark:text-purple-300">Completion Rate</span>
                            <span
                                class="text-sm font-semibold text-purple-900 dark:text-purple-100">{{ $quality['completion_rate'] }}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-purple-700 dark:text-purple-300">Avg Score</span>
                            <span
                                class="text-sm font-semibold text-purple-900 dark:text-purple-100">{{ number_format($quality['average_score'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-purple-700 dark:text-purple-300">Error Rate</span>
                            <span
                                class="text-sm font-semibold text-purple-900 dark:text-purple-100">{{ $quality['error_rate'] }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status Issues --}}
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                    <x-heroicon-o-information-circle class="h-4 w-4" />
                    System Status
                </h4>
                <ul class="space-y-1">
                    @foreach ($status['issues'] as $issue)
                        <li class="flex items-center gap-2 text-sm">
                            @if ($status['status'] === 'excellent')
                                <x-heroicon-o-check-circle class="h-4 w-4 text-green-500" />
                                <span class="text-green-700 dark:text-green-300">{{ $issue }}</span>
                            @elseif($status['status'] === 'warning')
                                <x-heroicon-o-exclamation-triangle class="h-4 w-4 text-yellow-500" />
                                <span class="text-yellow-700 dark:text-yellow-300">{{ $issue }}</span>
                            @else
                                <x-heroicon-o-x-circle class="h-4 w-4 text-red-500" />
                                <span class="text-red-700 dark:text-red-300">{{ $issue }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Quick Stats Bar --}}
            @if ($currentPeriod)
                <div
                    class="bg-primary-50 dark:bg-primary-900/20 rounded-lg p-3 border border-primary-200 dark:border-primary-800">
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-4">
                            <span class="text-primary-700 dark:text-primary-300">
                                <strong>Current Period:</strong> {{ $currentPeriod->nama_periode }}
                            </span>
                            @if ($currentPeriod->tanggal_selesai)
                                <span class="text-primary-600 dark:text-primary-400">
                                    <strong>Ends:</strong>
                                    {{ \Carbon\Carbon::parse($currentPeriod->tanggal_selesai)->format('M d, Y') }}
                                </span>
                            @endif
                        </div>
                        <span class="text-xs text-primary-500 dark:text-primary-400">
                            Last updated: {{ now()->format('H:i:s') }}
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
