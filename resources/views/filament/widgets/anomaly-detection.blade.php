<div class="filament-widget">
    <div class="space-y-6">
        <!-- Header with Risk Level Indicator -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center mr-4">
                    🔍
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Anomaly Detection
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        AI-powered detection of unusual patterns and outliers
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <div class="text-sm text-gray-500 dark:text-gray-400">System Risk Level</div>
                    <div class="flex items-center mt-1">
                        <span class="px-3 py-1 text-sm font-medium rounded-full
                            @if($anomalySummary['risk_level'] === 'critical') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                            @elseif($anomalySummary['risk_level'] === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                            @elseif($anomalySummary['risk_level'] === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                            @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                            {{ ucfirst($anomalySummary['risk_level']) }}
                        </span>
                        @if($anomalySummary['risk_level'] === 'critical') ⚠️
                        @elseif($anomalySummary['risk_level'] === 'high') 🔶
                        @elseif($anomalySummary['risk_level'] === 'medium') 🟡
                        @else ✅ @endif
                    </div>
                </div>
                <select wire:model.live="filter" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    <option value="all">All Anomalies</option>
                    <option value="critical">Critical Only</option>
                    <option value="performance">Performance</option>
                    <option value="quality">Quality Issues</option>
                    <option value="timeline">Timeline Issues</option>
                </select>
            </div>
        </div>

        <!-- Summary Dashboard -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 p-4 rounded-lg border border-red-200 dark:border-red-800">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-red-700 dark:text-red-300">
                            {{ $anomalySummary['total_anomalies'] }}
                        </div>
                        <div class="text-sm text-red-600 dark:text-red-400">Total Anomalies</div>
                    </div>
                    <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center">
                        <span class="text-white text-sm">🚨</span>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 p-4 rounded-lg border border-orange-200 dark:border-orange-800">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-orange-700 dark:text-orange-300">
                            {{ $anomalySummary['severity_breakdown']['critical'] + $anomalySummary['severity_breakdown']['high'] }}
                        </div>
                        <div class="text-sm text-orange-600 dark:text-orange-400">High Priority</div>
                    </div>
                    <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
                        <span class="text-white text-sm">⚡</span>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">
                            {{ $anomalySummary['investigation_needed'] }}
                        </div>
                        <div class="text-sm text-yellow-600 dark:text-yellow-400">Need Investigation</div>
                    </div>
                    <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                        <span class="text-white text-sm">🔍</span>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                            {{ count($anomalySummary['type_breakdown']) }}
                        </div>
                        <div class="text-sm text-blue-600 dark:text-blue-400">Anomaly Types</div>
                    </div>
                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                        <span class="text-white text-sm">📊</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Anomalies -->
        @if(!empty($performanceAnomalies))
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center mb-6">
                    <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center mr-3">
                        📈
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Performance Anomalies</h3>
                    <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 text-xs font-medium rounded-full">
                        {{ count($performanceAnomalies) }}
                    </span>
                </div>
                
                <div class="space-y-4">
                    @foreach($performanceAnomalies as $anomaly)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 
                                    @if($anomaly['severity'] === 'critical') bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800
                                    @elseif($anomaly['severity'] === 'high') bg-orange-50 dark:bg-orange-900/10 border-orange-200 dark:border-orange-800
                                    @else bg-yellow-50 dark:bg-yellow-900/10 border-yellow-200 dark:border-yellow-800 @endif">
                            
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-start">
                                    @if($anomaly['type'] === 'statistical_outlier')
                                        <span class="text-2xl mr-3">📊</span>
                                    @elseif($anomaly['type'] === 'sudden_drop')
                                        <span class="text-2xl mr-3">📉</span>
                                    @else
                                        <span class="text-2xl mr-3">⚠️</span>
                                    @endif
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-white">
                                            {{ $anomaly['school']->name ?? 'Unknown School' }}
                                        </h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $anomaly['description'] }}
                                        </p>
                                        @if(isset($anomaly['school']->type))
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ $anomaly['school']->type }} • {{ $anomaly['school']->region ?? 'Unknown Region' }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-col items-end space-y-2">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        @if($anomaly['severity'] === 'critical') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @elseif($anomaly['severity'] === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @endif">
                                        {{ ucfirst($anomaly['severity']) }}
                                    </span>
                                    @if($anomaly['investigation_needed'])
                                        <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full">
                                            Investigation Needed
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Details Grid -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                                @foreach($anomaly['details'] as $key => $value)
                                    <div class="text-center p-2 bg-white dark:bg-gray-700 rounded-lg">
                                        <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                                            {{ str_replace('_', ' ', $key) }}
                                        </div>
                                        <div class="font-semibold text-gray-900 dark:text-white">
                                            @if(is_numeric($value))
                                                {{ is_float($value) ? number_format($value, 1) : $value }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Quality Anomalies -->
        @if(!empty($qualityAnomalies))
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center mb-6">
                    <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center mr-3">
                        🔍
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quality Anomalies</h3>
                    <span class="ml-2 px-2 py-1 bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 text-xs font-medium rounded-full">
                        {{ count($qualityAnomalies) }}
                    </span>
                </div>
                
                <div class="space-y-4">
                    @foreach($qualityAnomalies as $anomaly)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-yellow-50 dark:bg-yellow-900/10 border-yellow-200 dark:border-yellow-800">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-start">
                                    @if($anomaly['type'] === 'suspicious_scoring')
                                        <span class="text-2xl mr-3">🎯</span>
                                    @else
                                        <span class="text-2xl mr-3">👤</span>
                                    @endif
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-white">
                                            @if(isset($anomaly['school']))
                                                {{ $anomaly['school']->name }}
                                            @else
                                                Quality Issue Detected
                                            @endif
                                        </h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $anomaly['description'] }}
                                        </p>
                                    </div>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    {{ ucfirst($anomaly['severity']) }}
                                </span>
                            </div>
                            
                            <!-- Details -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                                @foreach($anomaly['details'] as $key => $value)
                                    <div class="text-center p-2 bg-white dark:bg-gray-700 rounded-lg">
                                        <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                                            {{ str_replace('_', ' ', $key) }}
                                        </div>
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $value }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Timeline Anomalies -->
        @if(!empty($timelineAnomalies))
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center mb-6">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-3">
                        ⏰
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Timeline Anomalies</h3>
                    <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 text-xs font-medium rounded-full">
                        {{ count($timelineAnomalies) }}
                    </span>
                </div>
                
                <div class="space-y-4">
                    @foreach($timelineAnomalies as $anomaly)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 
                                    @if($anomaly['severity'] === 'critical') bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800
                                    @else bg-blue-50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800 @endif">
                            
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-start">
                                    @if($anomaly['type'] === 'overdue_assessment')
                                        <span class="text-2xl mr-3">⏱️</span>
                                    @else
                                        <span class="text-2xl mr-3">🐌</span>
                                    @endif
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-white">
                                            {{ $anomaly['school']->name ?? 'Timeline Issue' }}
                                        </h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $anomaly['description'] }}
                                        </p>
                                    </div>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($anomaly['severity'] === 'critical') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                                    {{ ucfirst($anomaly['severity']) }}
                                </span>
                            </div>
                            
                            <!-- Timeline Details -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                                @foreach($anomaly['details'] as $key => $value)
                                    <div class="text-center p-2 bg-white dark:bg-gray-700 rounded-lg">
                                        <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                                            {{ str_replace('_', ' ', $key) }}
                                        </div>
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $value }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Pattern and System Anomalies -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Pattern Anomalies -->
            @if(!empty($patternAnomalies))
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mr-3">
                            🔄
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pattern Anomalies</h3>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($patternAnomalies as $anomaly)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="font-medium text-gray-900 dark:text-white text-sm">
                                        @if(isset($anomaly['school']))
                                            {{ $anomaly['school']->name }}
                                        @else
                                            Pattern Anomaly
                                        @endif
                                    </h4>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        @if($anomaly['severity'] === 'critical') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @elseif($anomaly['severity'] === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 @endif">
                                        {{ ucfirst($anomaly['severity']) }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $anomaly['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- System Anomalies -->
            @if(!empty($systemAnomalies))
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mr-3">
                            ⚙️
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">System Anomalies</h3>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($systemAnomalies as $anomaly)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="font-medium text-gray-900 dark:text-white text-sm">
                                        {{ ucfirst(str_replace('_', ' ', $anomaly['type'])) }}
                                    </h4>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        @if($anomaly['severity'] === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 @endif">
                                        {{ ucfirst($anomaly['severity']) }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $anomaly['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Behavioral Anomalies -->
        @if(!empty($behavioralAnomalies))
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center mb-6">
                    <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mr-3">
                        👥
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Behavioral Anomalies</h3>
                </div>
                
                <div class="space-y-4">
                    @foreach($behavioralAnomalies as $anomaly)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-indigo-50 dark:bg-indigo-900/10">
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">{{ $anomaly['description'] }}</h4>
                            @if(isset($anomaly['details']['unusual_hours']))
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Unusual submission hours:</span>
                                    @foreach($anomaly['details']['unusual_hours'] as $hourData)
                                        <span class="inline-block bg-indigo-100 dark:bg-indigo-800 px-2 py-1 rounded text-xs mr-1 mt-1">
                                            {{ $hourData['hour'] }}:00 ({{ $hourData['count'] }} submissions)
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Empty State -->
        @if(empty($performanceAnomalies) && empty($qualityAnomalies) && empty($timelineAnomalies) && empty($patternAnomalies) && empty($systemAnomalies) && empty($behavioralAnomalies))
            <div class="bg-white dark:bg-gray-800 rounded-xl p-12 border border-gray-200 dark:border-gray-700 text-center">
                <div class="text-6xl mb-4">✅</div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Anomalies Detected</h3>
                <p class="text-gray-600 dark:text-gray-400">All systems are operating within normal parameters</p>
            </div>
        @endif
    </div>
</div>
