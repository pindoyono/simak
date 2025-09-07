<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-6">
            {{-- Header --}}
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-bolt class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                        Quick Actions
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Take action on urgent items and access key features
                    </p>
                </div>
                @if ($currentPeriod)
                    <div class="text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Current Period</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $currentPeriod->nama_periode }}
                        </p>
                    </div>
                @endif
            </div>

            {{-- Action Buttons Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Start New Assessment --}}
                @if ($canCreateAssessment)
                    <a href="{{ route('filament.admin.pages.assessment-wizard') }}"
                        class="group relative bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white rounded-lg p-4 transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-white">Start Assessment</h4>
                                <p class="text-xs text-primary-100 mt-1">Create new assessment</p>
                            </div>
                            <x-heroicon-o-rocket-launch
                                class="h-6 w-6 text-white group-hover:rotate-12 transition-transform" />
                        </div>
                    </a>
                @endif

                {{-- Review Pending --}}
                @if ($pendingAssessments > 0)
                    <a href="{{ route('filament.admin.resources.school-assessments.index') }}?tableFilters[status][value]=submitted"
                        class="group relative bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-lg p-4 transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-white">Review Pending</h4>
                                <p class="text-xs text-orange-100 mt-1">{{ $pendingAssessments }} assessments waiting
                                </p>
                            </div>
                            <div class="relative">
                                <x-heroicon-o-clipboard-document-check class="h-6 w-6 text-white" />
                                @if ($pendingAssessments > 0)
                                    <span
                                        class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                        {{ min($pendingAssessments, 9) }}{{ $pendingAssessments > 9 ? '+' : '' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endif

                {{-- Generate Report --}}
                @if ($canViewReports)
                    <a href="{{ route('filament.admin.pages.assessment-report') }}"
                        class="group relative bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-lg p-4 transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-white">Generate Report</h4>
                                <p class="text-xs text-emerald-100 mt-1">View analytics & insights</p>
                            </div>
                            <x-heroicon-o-chart-bar
                                class="h-6 w-6 text-white group-hover:scale-110 transition-transform" />
                        </div>
                    </a>
                @endif

                {{-- Manage Schools --}}
                <a href="{{ route('filament.admin.resources.schools.index') }}"
                    class="group relative bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-lg p-4 transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-semibold text-white">Manage Schools</h4>
                            <p class="text-xs text-blue-100 mt-1">View & edit school data</p>
                        </div>
                        <x-heroicon-o-building-office-2 class="h-6 w-6 text-white" />
                    </div>
                </a>

                {{-- View All Assessments --}}
                <a href="{{ route('filament.admin.resources.school-assessments.index') }}"
                    class="group relative bg-gradient-to-r from-purple-500 to-violet-500 hover:from-purple-600 hover:to-violet-600 text-white rounded-lg p-4 transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-semibold text-white">All Assessments</h4>
                            <p class="text-xs text-purple-100 mt-1">Browse all assessments</p>
                        </div>
                        <x-heroicon-o-clipboard-document-list class="h-6 w-6 text-white" />
                    </div>
                </a>

                {{-- Settings --}}
                <a href="{{ route('filament.admin.resources.assessment-periods.index') }}"
                    class="group relative bg-gradient-to-r from-gray-500 to-slate-500 hover:from-gray-600 hover:to-slate-600 text-white rounded-lg p-4 transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-semibold text-white">Settings</h4>
                            <p class="text-xs text-gray-100 mt-1">Manage periods & config</p>
                        </div>
                        <x-heroicon-o-cog-6-tooth
                            class="h-6 w-6 text-white group-hover:rotate-90 transition-transform" />
                    </div>
                </a>
            </div>

            {{-- Smart Suggestions --}}
            @if ($overdueAssessments > 0 || $schoolsWithoutAssessment > 0 || $pendingAssessments > 0)
                <div
                    class="bg-gradient-to-r from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <x-heroicon-o-light-bulb
                            class="h-5 w-5 text-yellow-600 dark:text-yellow-400 mt-0.5 flex-shrink-0" />
                        <div class="flex-1">
                            <h4 class="font-medium text-yellow-900 dark:text-yellow-100">💡 Suggested Actions</h4>
                            <ul class="mt-2 space-y-1 text-sm text-yellow-800 dark:text-yellow-200">
                                @if ($overdueAssessments > 0)
                                    <li>• Complete {{ $overdueAssessments }} overdue
                                        assessment{{ $overdueAssessments > 1 ? 's' : '' }}</li>
                                @endif
                                @if ($schoolsWithoutAssessment > 0)
                                    <li>• Start assessments for {{ $schoolsWithoutAssessment }}
                                        school{{ $schoolsWithoutAssessment > 1 ? 's' : '' }}</li>
                                @endif
                                @if ($pendingAssessments > 0)
                                    <li>• Review {{ $pendingAssessments }} pending
                                        assessment{{ $pendingAssessments > 1 ? 's' : '' }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
