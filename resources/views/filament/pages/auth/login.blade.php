<x-filament-panels::layout.base :livewire="$livewire">
    <div class="fi-simple-layout flex min-h-screen flex-col items-center">
        <div class="fi-simple-main-ctn flex w-full flex-grow items-center justify-center">
            <main
                class="fi-simple-main my-16 w-full bg-white px-6 py-12 shadow-2xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 sm:max-w-lg sm:rounded-xl sm:px-12">
                <section class="grid auto-cols-fr gap-y-6">
                    <!-- Logo Section -->
                    <div class="flex justify-center mb-8">
                        {{ \Filament\Facades\Filament::getCurrentPanel()->getBrandLogo() }}
                    </div>

                    <!-- Sign in Title with adjusted spacing -->
                    <div class="flex justify-center mb-6">
                        <h1
                            class="fi-simple-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                            {{ __('filament-panels::pages/auth/login.heading') }}
                        </h1>
                    </div>

                    <!-- Login Form -->
                    {{ $slot }}
                </section>
            </main>
        </div>
    </div>
</x-filament-panels::layout.base>
