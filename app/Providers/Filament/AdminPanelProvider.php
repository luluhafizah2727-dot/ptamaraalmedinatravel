<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile as AdminEditProfile;
use App\Filament\Pages\Auth\Login as AdminLogin;
use App\Filament\Pages\Dashboard;
use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
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
            ->login(AdminLogin::class)
            ->profile(page: AdminEditProfile::class, isSimple: false)
            ->brandName('PT Amara Al Medina Travel')
            ->brandLogo(fn (): string => SiteSetting::mediaUrl(SiteSetting::BRAND_LOGO_KEY, 'images/site/logo.png'))
            ->brandLogoHeight('4rem')
            ->favicon(fn (): string => SiteSetting::mediaUrl(SiteSetting::FAVICON_KEY, 'images/site/logo.png'))
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                'primary' => Color::hex('#d61a6a'),
                'success' => Color::hex('#0b8a4a'),
            ])
            ->userMenuItems([
                'profile' => fn (Action $action): Action => $action
                    ->label('My Account')
                    ->extraAttributes(['class' => 'admin-user-menu-hidden-default']),
                'logout' => fn (Action $action): Action => $action->hidden(),
            ])
            ->renderHook(
                PanelsRenderHook::USER_MENU_PROFILE_BEFORE,
                fn (): string => view('filament.partials.user-menu-profile-card')->render(),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                ValidateCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
