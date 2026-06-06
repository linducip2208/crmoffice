<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Admin\Widgets\KpiOverviewWidget;
use App\Filament\Admin\Widgets\TicketKpiWidget;
use App\Filament\Admin\Widgets\MyTasksTable;
use App\Filament\Admin\Widgets\PendingInvoicesTable;
use App\Filament\Admin\Widgets\RecentLeadsTable;
use App\Filament\Admin\Widgets\RevenueChartWidget;
use App\Filament\Admin\Widgets\StatsOverview;
use App\Filament\Admin\Widgets\SupportQueueTable;
use App\Filament\Admin\Pages\NotificationPreferences;
use Filament\Pages\Dashboard;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): \Illuminate\Contracts\View\View => view('filament.auth.login-demo-box'),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): \Illuminate\Contracts\View\View => view('filament.admin.partials.ai-chat-assistant'),
            )
            ->brandName('Admin')
            ->brandLogo(fn () => new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" fill="none" width="48" height="48"><rect width="48" height="48" rx="12" fill="url(#lg)"/><path d="M14 20l6-6 8 8-6 6-8-8z" fill="#fff" opacity="0.9"/><path d="M22 27l6-6 8 8-6 6-8-8z" fill="#fff" opacity="0.6"/><defs><linearGradient id="lg" x1="0" y1="0" x2="48" y2="48"><stop stop-color="#4f46e5"/><stop offset="1" stop-color="#7c3aed"/></linearGradient></defs></svg>'))
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('15.5rem')
            ->collapsedSidebarWidth('4rem')
            ->topbar(true)
            ->navigationGroups([
                NavigationGroup::make('🏢 Master Data')->collapsed(false),
                NavigationGroup::make('💼 CRM')->collapsed(false),
                NavigationGroup::make('💰 Penjualan')->collapsed(false),
                NavigationGroup::make('📊 Finance')->collapsed(true),
                NavigationGroup::make('📋 Proyek')->collapsed(false),
                NavigationGroup::make('🎧 Support')->collapsed(true),
                NavigationGroup::make('📈 Laporan')->collapsed(true),
                NavigationGroup::make('📣 Marketing')->collapsed(true),
                NavigationGroup::make('🔌 Integrasi')->collapsed(true),
                NavigationGroup::make('⚙️ Sistem')->collapsed(true),
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Dashboard::class,
                NotificationPreferences::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                AccountWidget::class,
                StatsOverview::class,
                RevenueChartWidget::class,
                KpiOverviewWidget::class,
                RecentLeadsTable::class,
                PendingInvoicesTable::class,
                MyTasksTable::class,
                SupportQueueTable::class,
                TicketKpiWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\RequireTwoFactor::class,
            ]);
    }
}
