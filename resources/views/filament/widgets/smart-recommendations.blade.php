<div class="filament-widget">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    🧠 Smart Recommendations
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    AI-powered insights and actionable recommendations
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $stats['total_recommendations'] }} recommendations •
                    {{ $stats['urgent_actions'] }} urgent actions
                </div>
                <select wire:model.live="filter"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    <option value="all">All Recommendations</option>
                    <option value="urgent">Urgent Actions</option>
                    <option value="quick_wins">Quick Wins</option>
                    <option value="long_term">Long-term Projects</option>
                </select>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div
                class="bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 p-4 rounded-lg border border-red-200 dark:border-red-800">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center mr-3">
                        <span class="text-white text-sm font-bold">!</span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $stats['urgent_actions'] }}
                        </div>
                        <div class="text-sm text-red-600 dark:text-red-400">Urgent Actions</div>
                    </div>
                </div>
            </div>

            <div
                class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center mr-3">
                        <span class="text-white text-sm">⚡</span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $stats['quick_wins'] }}
                        </div>
                        <div class="text-sm text-green-600 dark:text-green-400">Quick Wins</div>
                    </div>
                </div>
            </div>

            <div
                class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                        <span class="text-white text-sm">📈</span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                            {{ $stats['estimated_impact'] }}</div>
                        <div class="text-sm text-blue-600 dark:text-blue-400">Est. Impact</div>
                    </div>
                </div>
            </div>

            <div
                class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center mr-3">
                        <span class="text-white text-sm">⏱️</span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-purple-700 dark:text-purple-300">
                            {{ $stats['implementation_timeline'] }}</div>
                        <div class="text-sm text-purple-600 dark:text-purple-400">Timeline</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Priority Recommendations -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center mb-6">
                <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center mr-3">
                    🚨
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Priority Recommendations</h3>
            </div>

            <div class="space-y-6">
                @foreach ($priorityRecommendations as $recommendation)
                    <div
                        class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-start">
                                @if ($recommendation['type'] === 'critical_intervention')
                                    <span class="text-2xl mr-3">🚨</span>
                                @elseif($recommendation['type'] === 'category_improvement')
                                    <span class="text-2xl mr-3">📊</span>
                                @else
                                    <span class="text-2xl mr-3">⚖️</span>
                                @endif
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ $recommendation['title'] }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {{ $recommendation['description'] }}</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end space-y-2">
                                <span
                                    class="px-3 py-1 text-xs font-medium rounded-full
                                    @if ($recommendation['priority'] === 'urgent') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @elseif($recommendation['priority'] === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                    @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @endif">
                                    {{ ucfirst($recommendation['priority']) }} Priority
                                </span>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Success: {{ $recommendation['success_probability'] }}%
                                </div>
                            </div>
                        </div>

                        <!-- School Info (if applicable) -->
                        @if (isset($recommendation['school']))
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h5 class="font-medium text-gray-900 dark:text-white">
                                            {{ $recommendation['school']->name }}</h5>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $recommendation['school']->type }} •
                                            {{ $recommendation['school']->region ?? 'Unknown Region' }}
                                        </p>
                                    </div>
                                    @if (isset($recommendation['school']->assessments) && $recommendation['school']->assessments->count() > 0)
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-red-600 dark:text-red-400">
                                                {{ $recommendation['school']->assessments->first()->total_score }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Latest Score</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Impact and Effort -->
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Impact</div>
                                <div class="font-semibold text-blue-700 dark:text-blue-300 capitalize">
                                    {{ $recommendation['impact'] }}
                                </div>
                            </div>
                            <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Effort</div>
                                <div class="font-semibold text-green-700 dark:text-green-300 capitalize">
                                    {{ $recommendation['effort'] }}
                                </div>
                            </div>
                            <div class="text-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Timeframe</div>
                                <div class="font-semibold text-purple-700 dark:text-purple-300 capitalize">
                                    {{ $recommendation['timeframe'] }}
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mb-4">
                            <h6 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Recommended Actions:
                            </h6>
                            <ul class="space-y-2">
                                @foreach ($recommendation['actions'] as $action)
                                    <li class="flex items-start">
                                        <span class="text-blue-500 mr-2 mt-1">•</span>
                                        <span
                                            class="text-sm text-gray-600 dark:text-gray-400">{{ $action }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- Expected Outcome -->
                        <div
                            class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                            <div class="flex items-start">
                                <span class="text-green-500 mr-2">🎯</span>
                                <div>
                                    <h6 class="text-sm font-medium text-green-800 dark:text-green-200">Expected Outcome
                                    </h6>
                                    <p class="text-sm text-green-700 dark:text-green-300">
                                        {{ $recommendation['expected_outcome'] }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Resources (if applicable) -->
                        @if (isset($recommendation['resources_needed']))
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Resources needed:</span>
                                @foreach ($recommendation['resources_needed'] as $resource)
                                    <span
                                        class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md text-xs">
                                        {{ $resource }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Actionable Insights -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center mb-6">
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-3">
                    💡
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Actionable Insights</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($actionableInsights as $insight)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex items-start mb-3">
                            <span class="text-2xl mr-3">{{ $insight['icon'] }}</span>
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $insight['title'] }}</h4>
                                @if ($insight['action_required'])
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 mt-1">
                                        Action Required
                                    </span>
                                @endif
                            </div>
                        </div>

                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $insight['insight'] }}</p>

                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                            <h6 class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Potential Impact:</h6>
                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ $insight['potential_impact'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Optimization Suggestions -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center mb-6">
                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-3">
                    ⚙️
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Optimization Suggestions</h3>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach ($optimizationSuggestions as $suggestion)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex items-start justify-between mb-3">
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $suggestion['title'] }}</h4>
                            <span
                                class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                {{ $suggestion['category'] }}
                            </span>
                        </div>

                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $suggestion['description'] }}</p>

                        <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Difficulty:</span>
                                <span
                                    class="font-medium ml-1 capitalize
                                    @if ($suggestion['implementation_difficulty'] === 'low') text-green-600 dark:text-green-400
                                    @elseif($suggestion['implementation_difficulty'] === 'medium') text-yellow-600 dark:text-yellow-400
                                    @else text-red-600 dark:text-red-400 @endif">
                                    {{ $suggestion['implementation_difficulty'] }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Cost:</span>
                                <span class="font-medium ml-1 capitalize text-gray-900 dark:text-white">
                                    {{ $suggestion['cost_impact'] }}
                                </span>
                            </div>
                        </div>

                        @if (isset($suggestion['expected_time_savings']))
                            <div
                                class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3 mb-3">
                                <div class="text-sm">
                                    <span class="font-medium text-green-800 dark:text-green-200">Time Savings:</span>
                                    <span
                                        class="text-green-700 dark:text-green-300 ml-1">{{ $suggestion['expected_time_savings'] }}</span>
                                </div>
                            </div>
                        @endif

                        @if (isset($suggestion['expected_efficiency_gain']))
                            <div
                                class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mb-3">
                                <div class="text-sm">
                                    <span class="font-medium text-blue-800 dark:text-blue-200">Efficiency Gain:</span>
                                    <span
                                        class="text-blue-700 dark:text-blue-300 ml-1">{{ $suggestion['expected_efficiency_gain'] }}</span>
                                </div>
                            </div>
                        @endif

                        @if (isset($suggestion['expected_quality_improvement']))
                            <div
                                class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-3 mb-3">
                                <div class="text-sm">
                                    <span class="font-medium text-purple-800 dark:text-purple-200">Quality
                                        Improvement:</span>
                                    <span
                                        class="text-purple-700 dark:text-purple-300 ml-1">{{ $suggestion['expected_quality_improvement'] }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                            <h6 class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Implementation Steps:
                            </h6>
                            <ol class="text-xs space-y-1">
                                @foreach ($suggestion['steps'] as $index => $step)
                                    <li class="flex items-start">
                                        <span
                                            class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full w-4 h-4 flex items-center justify-center text-xs font-medium mr-2 mt-0.5">
                                            {{ $index + 1 }}
                                        </span>
                                        <span class="text-gray-600 dark:text-gray-400">{{ $step }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Best Practices -->
        @if (!empty($bestPractices))
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center mb-6">
                    <div
                        class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center mr-3">
                        🌟
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Best Practices</h3>
                </div>

                <div class="space-y-4">
                    @foreach ($bestPractices as $practice)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-start justify-between mb-3">
                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $practice['title'] }}</h4>
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    {{ ucfirst($practice['type']) }}
                                </span>
                            </div>

                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $practice['description'] }}
                            </p>

                            @if ($practice['type'] === 'school_excellence' && isset($practice['school']))
                                <div
                                    class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span
                                                class="font-medium text-yellow-800 dark:text-yellow-200">School:</span>
                                            <span
                                                class="text-yellow-700 dark:text-yellow-300 ml-1">{{ $practice['school']->name }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-yellow-800 dark:text-yellow-200">Applicable
                                                to:</span>
                                            <span
                                                class="text-yellow-700 dark:text-yellow-300 ml-1">{{ $practice['applicable_to'] }}
                                                similar schools</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
