<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->brandName('SIMAK-PM')
            ->brandLogo(fn () => view('filament.brand-logo'))
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets') // Disabled to manually control widget visibility
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\SmartStatsOverview::class,
                \App\Filament\Widgets\QuickActionsWidget::class,
                \App\Filament\Widgets\AlertsWidget::class,
                \App\Filament\Widgets\AssessmentProgressChart::class,
                \App\Filament\Widgets\TrendsWidget::class,
                \App\Filament\Widgets\SchoolPerformanceWidget::class,
                \App\Filament\Widgets\SystemHealthWidget::class,
                // Hidden AI-powered widgets (Phase 3)
                // \App\Filament\Widgets\PredictiveAnalyticsWidget::class,
                // \App\Filament\Widgets\SmartRecommendationsWidget::class,
                // \App\Filament\Widgets\AnomalyDetectionWidget::class,
                // \App\Filament\Widgets\IntelligentInsightsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
