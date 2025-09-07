<div class="filament-widget">
    <div class="space-y-6">
        <!-- Header with Filters -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    🔮 Predictive Analytics
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    AI-powered predictions and forecasts
                </p>
            </div>
            <div class="flex space-x-2">
                <select wire:model.live="filter"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    <option value="next_quarter">Next Quarter</option>
                    <option value="next_semester">Next Semester</option>
                    <option value="next_year">Next Year</option>
                </select>
            </div>
        </div>

        <!-- Predictions Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- School Performance Predictions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-3">
                        🎯
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Performance Predictions</h3>
                </div>

                <div class="space-y-4">
                    @foreach ($predictions as $prediction)
                        <div
                            class="border border-gray-100 dark:border-gray-700 rounded-lg p-4 
                                    @if ($prediction['risk_level'] === 'high') border-l-4 border-l-red-500
                                    @elseif($prediction['risk_level'] === 'medium') border-l-4 border-l-yellow-500
                                    @else border-l-4 border-l-green-500 @endif">

                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white">
                                        {{ $prediction['school']->name }}
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $prediction['school']->type }} •
                                        {{ $prediction['school']->region ?? 'Unknown Region' }}
                                    </p>
                                </div>
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full
                                    @if ($prediction['confidence'] === 'high') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($prediction['confidence'] === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                    {{ ucfirst($prediction['confidence']) }} Confidence
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Current Avg:</span>
                                    <span class="font-medium text-gray-900 dark:text-white ml-1">
                                        {{ $prediction['current_avg'] }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Predicted:</span>
                                    <span
                                        class="font-medium ml-1
                                        @if ($prediction['predicted_score'] > $prediction['current_avg']) text-green-600 dark:text-green-400
                                        @elseif($prediction['predicted_score'] < $prediction['current_avg']) text-red-600 dark:text-red-400
                                        @else text-gray-900 dark:text-white @endif">
                                        {{ $prediction['predicted_score'] }}
                                        @if ($prediction['trend'] > 0)
                                            ↗️
                                        @elseif($prediction['trend'] < 0)
                                            ↘️
                                        @else
                                            ➡️
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                    <span>Improvement Probability</span>
                                    <span>{{ $prediction['improvement_probability'] }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-300
                                        @if ($prediction['improvement_probability'] >= 70) bg-green-500
                                        @elseif($prediction['improvement_probability'] >= 40) bg-yellow-500
                                        @else bg-red-500 @endif"
                                        style="width: {{ $prediction['improvement_probability'] }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Forecasts Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center mb-4">
                    <div
                        class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mr-3">
                        📈
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Score Forecasts</h3>
                </div>

                @if (!empty($forecasts['historical']))
                    <div class="relative h-64">
                        <canvas id="forecastChart"></canvas>
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-4 text-center">
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Trend</div>
                            <div class="font-semibold text-gray-900 dark:text-white capitalize">
                                {{ $forecasts['trend_direction'] }}
                                @if ($forecasts['trend_direction'] === 'improving')
                                    📈
                                @elseif($forecasts['trend_direction'] === 'declining')
                                    📉
                                @else
                                    ➡️
                                @endif
                            </div>
                        </div>
                        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Next Month</div>
                            <div class="font-semibold text-gray-900 dark:text-white">
                                @if (!empty($forecasts['forecasts']))
                                    {{ $forecasts['forecasts'][0]['predicted_score'] ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                            <div class="text-sm text-gray-500 dark:text-gray-400">3 Months</div>
                            <div class="font-semibold text-gray-900 dark:text-white">
                                @if (!empty($forecasts['forecasts']) && count($forecasts['forecasts']) >= 3)
                                    {{ $forecasts['forecasts'][2]['predicted_score'] ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <div class="text-4xl mb-2">📊</div>
                        <p>Insufficient historical data for forecasting</p>
                        <p class="text-sm">Need at least 3 months of data</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Smart Recommendations -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center mb-4">
                <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900 rounded-lg flex items-center justify-center mr-3">
                    💡
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Smart Recommendations</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($recommendations as $recommendation)
                    <div
                        class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center">
                                @if ($recommendation['type'] === 'performance_improvement')
                                    <span class="text-red-500 mr-2">🚨</span>
                                @elseif($recommendation['type'] === 'resource_allocation')
                                    <span class="text-blue-500 mr-2">⚖️</span>
                                @else
                                    <span class="text-green-500 mr-2">⚙️</span>
                                @endif
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full
                                    @if ($recommendation['priority'] === 'high') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @elseif($recommendation['priority'] === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                                    {{ ucfirst($recommendation['priority']) }}
                                </span>
                            </div>
                        </div>

                        <h4 class="font-medium text-gray-900 dark:text-white mb-2">
                            {{ $recommendation['title'] }}
                        </h4>

                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            {{ $recommendation['description'] }}
                        </p>

                        <div class="space-y-2">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Suggested Actions:</div>
                            <ul class="text-xs space-y-1">
                                @foreach ($recommendation['actions'] as $action)
                                    <li class="flex items-start">
                                        <span class="text-blue-500 mr-2">•</span>
                                        <span class="text-gray-600 dark:text-gray-400">{{ $action }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div
                            class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 flex justify-between text-xs">
                            <span class="text-gray-500 dark:text-gray-400">
                                Impact: <span
                                    class="font-medium">{{ ucfirst($recommendation['expected_impact']) }}</span>
                            </span>
                            <span class="text-gray-500 dark:text-gray-400">
                                {{ $recommendation['timeframe'] }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Risk Analysis -->
        @if (!empty($riskAnalysis))
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center mr-3">
                        ⚠️
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Risk Analysis</h3>
                </div>

                <div class="space-y-4">
                    @foreach ($riskAnalysis as $risk)
                        <div
                            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 
                                    @if ($risk['level'] === 'high') bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800
                                    @elseif($risk['level'] === 'medium') bg-yellow-50 dark:bg-yellow-900/10 border-yellow-200 dark:border-yellow-800
                                    @else bg-green-50 dark:bg-green-900/10 border-green-200 dark:border-green-800 @endif">

                            <div class="flex items-start justify-between mb-2">
                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $risk['title'] }}</h4>
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full
                                    @if ($risk['level'] === 'high') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @elseif($risk['level'] === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                                    {{ ucfirst($risk['level']) }} Risk
                                </span>
                            </div>

                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $risk['description'] }}</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Impact:</span>
                                    <span class="text-gray-600 dark:text-gray-400 ml-1">{{ $risk['impact'] }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Mitigation:</span>
                                    <span
                                        class="text-gray-600 dark:text-gray-400 ml-1">{{ $risk['mitigation'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

@if (!empty($forecasts['historical']))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('forecastChart');
            if (ctx) {
                const isDark = document.documentElement.classList.contains('dark');
                const textColor = isDark ? '#E5E7EB' : '#374151';
                const gridColor = isDark ? '#374151' : '#E5E7EB';

                // Prepare data
                const historical = @json($forecasts['historical']);
                const forecasts = @json($forecasts['forecasts'] ?? []);

                const historicalLabels = historical.map(item => item.month);
                const historicalScores = historical.map(item => item.score);

                const forecastLabels = forecasts.map(item => item.month);
                const forecastScores = forecasts.map(item => item.predicted_score);

                const allLabels = [...historicalLabels, ...forecastLabels];
                const historicalData = [...historicalScores, ...Array(forecastScores.length).fill(null)];
                const forecastData = [...Array(historicalScores.length).fill(null), ...forecastScores];

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: allLabels,
                        datasets: [{
                                label: 'Historical Scores',
                                data: historicalData,
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.4,
                                fill: false,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            },
                            {
                                label: 'Predicted Scores',
                                data: forecastData,
                                borderColor: 'rgb(147, 51, 234)',
                                backgroundColor: 'rgba(147, 51, 234, 0.1)',
                                tension: 0.4,
                                fill: false,
                                borderDash: [5, 5],
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                labels: {
                                    color: textColor,
                                    usePointStyle: true,
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: gridColor,
                                },
                                ticks: {
                                    color: textColor,
                                }
                            },
                            y: {
                                grid: {
                                    color: gridColor,
                                },
                                ticks: {
                                    color: textColor,
                                },
                                beginAtZero: false,
                                min: 30,
                                max: 100,
                            }
                        }
                    }
                });
            }
        });
    </script>
@endif
