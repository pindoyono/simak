<div class="filament-widget">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-purple-400 to-indigo-600 rounded-lg flex items-center justify-center mr-4">
                    <span class="text-white text-lg">🧠</span>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Intelligent Insights
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        AI-generated insights and comprehensive analysis
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Last updated: {{ $lastUpdated->format('H:i') }}
                </div>
                <select wire:model.live="filter"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    <option value="all">All Insights</option>
                    <option value="performance">Performance Focus</option>
                    <option value="trends">Trend Analysis</option>
                    <option value="predictions">Predictions</option>
                    <option value="recommendations">Recommendations</option>
                </select>
            </div>
        </div>

        <!-- Key Insights -->
        <div class="space-y-4">
            @foreach ($keyInsights as $insight)
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-start">
                            @if ($insight['type'] === 'system_health')
                                <div
                                    class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-4">
                                    <span class="text-blue-600 dark:text-blue-400 text-lg">🏥</span>
                                </div>
                            @elseif($insight['type'] === 'performance_quality')
                                <div
                                    class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-4">
                                    <span class="text-green-600 dark:text-green-400 text-lg">📊</span>
                                </div>
                            @elseif($insight['type'] === 'system_efficiency')
                                <div
                                    class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mr-4">
                                    <span class="text-purple-600 dark:text-purple-400 text-lg">⚡</span>
                                </div>
                            @elseif($insight['type'] === 'regional_analysis')
                                <div
                                    class="w-10 h-10 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center mr-4">
                                    <span class="text-orange-600 dark:text-orange-400 text-lg">🗺️</span>
                                </div>
                            @else
                                <div
                                    class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mr-4">
                                    <span class="text-gray-600 dark:text-gray-400 text-lg">👥</span>
                                </div>
                            @endif
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    {{ $insight['title'] }}
                                </h3>
                                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                                    {{ $insight['insight'] }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end space-y-2">
                            <span
                                class="px-3 py-1 text-xs font-medium rounded-full
                                @if ($insight['priority'] === 'high') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                @elseif($insight['priority'] === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                                {{ ucfirst($insight['priority']) }} Priority
                            </span>
                            @if ($insight['actionable'])
                                <span
                                    class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full">
                                    Action Required
                                </span>
                            @endif
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Trend: {{ ucfirst($insight['trend']) }}
                                @if ($insight['trend'] === 'improving')
                                    📈
                                @elseif($insight['trend'] === 'declining')
                                    📉
                                @else
                                    ➡️
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    @if (!empty($insight['details']))
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            @foreach ($insight['details'] as $key => $value)
                                <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 capitalize mb-1">
                                        {{ str_replace('_', ' ', $key) }}
                                    </div>
                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        @if (is_numeric($value))
                                            {{ is_float($value) ? number_format($value, 1) : number_format($value) }}
                                            @if (str_contains($key, 'rate') || str_contains($key, 'percentage'))
                                                %
                                            @endif
                                        @else
                                            {{ $value }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Recommendation -->
                    <div
                        class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <div class="flex items-start">
                            <span class="text-blue-500 mr-2 mt-0.5">💡</span>
                            <div>
                                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-1">Recommendation
                                </h4>
                                <p class="text-sm text-blue-700 dark:text-blue-300">{{ $insight['recommendation'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Performance Analysis -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center mb-6">
                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-3">
                    📈
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Performance Analysis</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400 mb-2">
                        {{ $performanceAnalysis['overall_performance']['average_score'] }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Overall Average</div>
                    <div class="mt-2">
                        <span
                            class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full">
                            Grade {{ $performanceAnalysis['overall_performance']['performance_grade'] }}
                        </span>
                    </div>
                </div>

                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-2">
                        {{ $performanceAnalysis['performance_distribution']['excellent'] }}%
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Excellent Performance</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">(≥85 points)</div>
                </div>

                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mb-2">
                        {{ $performanceAnalysis['performance_distribution']['needs_improvement'] }}%
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Need Improvement</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">(<60 points)</div>
                    </div>

                    <div class="text-center">
                        <div class="text-3xl font-bold text-purple-600 dark:text-purple-400 mb-2">
                            {{ $performanceAnalysis['school_type_performance']['gap'] }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Public-Private Gap</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">points difference</div>
                    </div>
                </div>
            </div>

            <!-- Trend Analysis -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center mb-6">
                    <div
                        class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mr-3">
                        📊
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Trend Analysis</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-medium text-green-800 dark:text-green-200">Monthly Trend</h4>
                            <span class="text-green-500">📈</span>
                        </div>
                        <div class="text-2xl font-bold text-green-700 dark:text-green-300 mb-1">
                            +{{ $trendAnalysis['monthly_trends']['monthly_growth_rate'] }}%
                        </div>
                        <div class="text-sm text-green-600 dark:text-green-400 capitalize">
                            {{ $trendAnalysis['monthly_trends']['trend_direction'] }}
                        </div>
                    </div>

                    <div
                        class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-medium text-blue-800 dark:text-blue-200">Seasonal Pattern</h4>
                            <span class="text-blue-500">📅</span>
                        </div>
                        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300 mb-1">
                            {{ $trendAnalysis['seasonal_patterns']['peak_season'] }}
                        </div>
                        <div class="text-sm text-blue-600 dark:text-blue-400">
                            Peak Season
                        </div>
                    </div>

                    <div
                        class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-medium text-purple-800 dark:text-purple-200">Long-term</h4>
                            <span class="text-purple-500">🚀</span>
                        </div>
                        <div class="text-2xl font-bold text-purple-700 dark:text-purple-300 mb-1 capitalize">
                            {{ $trendAnalysis['long_term_trajectory']['direction'] }}
                        </div>
                        <div class="text-sm text-purple-600 dark:text-purple-400 capitalize">
                            {{ $trendAnalysis['long_term_trajectory']['sustainability'] }} sustainability
                        </div>
                    </div>

                    <div
                        class="bg-gradient-to-r from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 p-4 rounded-lg border border-orange-200 dark:border-orange-800">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-medium text-orange-800 dark:text-orange-200">Emerging</h4>
                            <span class="text-orange-500">🔍</span>
                        </div>
                        <div class="text-lg font-bold text-orange-700 dark:text-orange-300 mb-1">
                            {{ count($trendAnalysis['emerging_patterns']['new_trends']) }}
                        </div>
                        <div class="text-sm text-orange-600 dark:text-orange-400">
                            New Patterns
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actionable Recommendations -->
            @if (!empty($actionableRecommendations))
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center mb-6">
                        <div
                            class="w-8 h-8 bg-amber-100 dark:bg-amber-900 rounded-lg flex items-center justify-center mr-3">
                            💡
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Actionable Recommendations</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($actionableRecommendations as $recommendation)
                            <div
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-3">
                                    <h4 class="font-medium text-gray-900 dark:text-white">
                                        {{ $recommendation['title'] }}</h4>
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full
                                    @if ($recommendation['priority'] === 'high') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @elseif($recommendation['priority'] === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                                        {{ ucfirst($recommendation['priority']) }}
                                    </span>
                                </div>

                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {{ $recommendation['description'] }}</p>

                                <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Impact:</span>
                                        <span
                                            class="font-medium ml-1 capitalize text-gray-900 dark:text-white">{{ $recommendation['impact'] }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Effort:</span>
                                        <span
                                            class="font-medium ml-1 capitalize text-gray-900 dark:text-white">{{ $recommendation['effort'] }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Timeline:</span>
                                        <span
                                            class="font-medium ml-1 text-gray-900 dark:text-white">{{ $recommendation['timeline'] }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Success:</span>
                                        <span
                                            class="font-medium ml-1 text-gray-900 dark:text-white">{{ $recommendation['success_probability'] }}%</span>
                                    </div>
                                </div>

                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-300
                                    @if ($recommendation['success_probability'] >= 80) bg-green-500
                                    @elseif($recommendation['success_probability'] >= 60) bg-yellow-500
                                    @else bg-red-500 @endif"
                                        style="width: {{ $recommendation['success_probability'] }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Contextual Alerts -->
            @if (!empty($contextualAlerts))
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center mb-6">
                        <div
                            class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center mr-3">
                            🚨
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Contextual Alerts</h3>
                    </div>

                    <div class="space-y-4">
                        @foreach ($contextualAlerts as $alert)
                            <div
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 
                                    @if ($alert['urgency'] === 'high') bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800
                                    @elseif($alert['urgency'] === 'medium') bg-yellow-50 dark:bg-yellow-900/10 border-yellow-200 dark:border-yellow-800
                                    @else bg-blue-50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800 @endif">

                                <div class="flex items-start justify-between">
                                    <div class="flex items-start">
                                        @if ($alert['type'] === 'deadline')
                                            <span class="text-2xl mr-3">⏰</span>
                                        @elseif($alert['type'] === 'quality')
                                            <span class="text-2xl mr-3">🔍</span>
                                        @else
                                            <span class="text-2xl mr-3">⚙️</span>
                                        @endif
                                        <div>
                                            <h4 class="font-medium text-gray-900 dark:text-white mb-1">
                                                {{ $alert['title'] }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $alert['message'] }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end space-y-2">
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full
                                        @if ($alert['urgency'] === 'high') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @elseif($alert['urgency'] === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                                            {{ ucfirst($alert['urgency']) }}
                                        </span>
                                        @if ($alert['action_required'])
                                            <span
                                                class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 rounded-full">
                                                Action Required
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Benchmark Analysis -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center mb-6">
                    <div
                        class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mr-3">
                        🎯
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Benchmark Analysis</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        class="text-center p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800">
                        <div class="text-sm text-indigo-600 dark:text-indigo-400 mb-2">vs Industry</div>
                        <div class="text-2xl font-bold text-indigo-700 dark:text-indigo-300 mb-1">
                            {{ $benchmarkAnalysis['industry_benchmarks']['percentile_rank'] }}th
                        </div>
                        <div class="text-xs text-indigo-600 dark:text-indigo-400">Percentile</div>
                    </div>

                    <div
                        class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <div class="text-sm text-green-600 dark:text-green-400 mb-2">vs Last Year</div>
                        <div class="text-2xl font-bold text-green-700 dark:text-green-300 mb-1">
                            +{{ $benchmarkAnalysis['historical_benchmarks']['improvement'] }}
                        </div>
                        <div class="text-xs text-green-600 dark:text-green-400">Points</div>
                    </div>

                    <div
                        class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="text-sm text-blue-600 dark:text-blue-400 mb-2">vs Peers</div>
                        <div class="text-lg font-bold text-blue-700 dark:text-blue-300 mb-1 capitalize">
                            {{ str_replace('_', ' ', $benchmarkAnalysis['peer_benchmarks']['ranking']) }}
                        </div>
                        <div class="text-xs text-blue-600 dark:text-blue-400">Ranking</div>
                    </div>

                    <div
                        class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                        <div class="text-sm text-purple-600 dark:text-purple-400 mb-2">Target Progress</div>
                        <div class="text-2xl font-bold text-purple-700 dark:text-purple-300 mb-1">
                            {{ $benchmarkAnalysis['target_benchmarks']['progress'] }}%
                        </div>
                        <div class="text-xs text-purple-600 dark:text-purple-400">Complete</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
