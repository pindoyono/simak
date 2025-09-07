<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-6">
            {{-- Header --}}
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-chart-bar-square class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                        School Performance Analytics
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Comprehensive overview of school performance and progress
                    </p>
                </div>
                @if($currentPeriod)
                    <div class="text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Period</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $currentPeriod->nama_periode }}</p>
                    </div>
                @endif
            </div>

            {{-- Main Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Top Performing Schools --}}
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
                    <h4 class="font-semibold text-green-900 dark:text-green-100 flex items-center gap-2 mb-3">
                        <x-heroicon-o-trophy class="h-4 w-4" />
                        Top Performers
                    </h4>
                    @if($topSchools->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($topSchools as $index => $assessment)
                                <div class="flex items-center justify-between p-2 bg-white/60 dark:bg-gray-800/60 rounded">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full 
                                            {{ $index === 0 ? 'bg-yellow-500 text-white' : 
                                               ($index === 1 ? 'bg-gray-400 text-white' : 
                                                ($index === 2 ? 'bg-amber-600 text-white' : 'bg-green-500 text-white')) }} 
                                            text-xs font-bold">
                                            {{ $index + 1 }}
                                        </span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ $assessment->school->nama_sekolah }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-green-600 dark:text-green-400">
                                            {{ number_format($assessment->total_score, 2) }}
                                        </span>
                                        <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200">
                                            @if($assessment->total_score >= 3.5) A
                                            @elseif($assessment->total_score >= 2.5) B
                                            @elseif($assessment->total_score >= 1.5) C
                                            @else D
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-green-700 dark:text-green-300">No assessments completed yet.</p>
                    @endif
                </div>

                {{-- Schools Needing Attention --}}
                <div class="bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-lg p-4 border border-red-200 dark:border-red-800">
                    <h4 class="font-semibold text-red-900 dark:text-red-100 flex items-center gap-2 mb-3">
                        <x-heroicon-o-exclamation-triangle class="h-4 w-4" />
                        Needs Attention
                    </h4>
                    @if($needsAttentionSchools->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($needsAttentionSchools as $assessment)
                                <div class="flex items-center justify-between p-2 bg-white/60 dark:bg-gray-800/60 rounded">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $assessment->school->nama_sekolah }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-red-600 dark:text-red-400">
                                            {{ number_format($assessment->total_score, 2) }}
                                        </span>
                                        <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200">
                                            @if($assessment->total_score >= 1.5) C @else D @endif
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-red-700 dark:text-red-300">All schools performing well!</p>
                    @endif
                </div>
            </div>

            {{-- Grade Distribution & Progress --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Grade Distribution --}}
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Grade Distribution</h4>
                    <div class="space-y-2">
                        @php
                            $totalAssessed = $gradeDistribution->sum();
                            $grades = ['A' => 'green', 'B' => 'blue', 'C' => 'yellow', 'D' => 'red'];
                        @endphp
                        @foreach($grades as $grade => $color)
                            @php
                                $count = $gradeDistribution->get($grade, 0);
                                $percentage = $totalAssessed > 0 ? round(($count / $totalAssessed) * 100, 1) : 0;
                            @endphp
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-{{ $color }}-500"></span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Grade {{ $grade }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-900 dark:text-white">{{ $count }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">({{ $percentage }}%)</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-{{ $color }}-500 h-2 rounded-full transition-all duration-300" 
                                     style="width: {{ $percentage }}%"></div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Progress by School Status --}}
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Progress by School Type</h4>
                    <div class="space-y-3">
                        @foreach($progressByStatus as $status)
                            @php
                                $percentage = $status->total_count > 0 ? round(($status->completed_count / $status->total_count) * 100, 1) : 0;
                                $statusColor = $status->status === 'Negeri' ? 'blue' : 'purple';
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $status->status }}
                                    </span>
                                    <span class="text-sm text-gray-900 dark:text-white">
                                        {{ $status->completed_count }}/{{ $status->total_count }} ({{ $percentage }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-{{ $statusColor }}-500 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <x-heroicon-o-clock class="h-4 w-4" />
                    Recent Activity
                </h4>
                @if($recentActivity->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($recentActivity as $activity)
                            <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-700 rounded">
                                <div class="flex-1">
                                    <span class="text-sm text-gray-900 dark:text-white">
                                        {{ $activity->school->nama_sekolah }}
                                    </span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        by {{ $activity->assessor->name ?? 'Unknown' }} • {{ $activity->updated_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($activity->total_score > 0)
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ number_format($activity->total_score, 2) }}
                                        </span>
                                    @endif
                                    <span class="px-2 py-1 rounded-full text-xs
                                        @switch($activity->status)
                                            @case('draft')
                                                bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                                @break
                                            @case('submitted')
                                                bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                @break
                                            @case('reviewed')
                                                bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                @break
                                            @case('approved')
                                                bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @break
                                        @endswitch">
                                        {{ ucfirst($activity->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No recent activity.</p>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
