@php
    $logoUrl = $siteMedia['logo'] ?? asset('images/site/logo.png');
    $faviconUrl = $siteMedia['favicon'] ?? asset('images/site/logo.png');
    $whatsappRaw = $contact?->whatsapp ?? $settings->get('cta_whatsapp', '');
    $whatsapp = preg_replace('/\D+/', '', $whatsappRaw);
    $whatsapp = str_starts_with($whatsapp, '0') ? '62' . substr($whatsapp, 1) : $whatsapp;
    $phoneRaw = $contact?->whatsapp ?? '';
    $phoneDigits = preg_replace('/\D+/', '', $phoneRaw);
    $phoneInternational = str_starts_with($phoneDigits, '0') ? '62' . substr($phoneDigits, 1) : $phoneDigits;
    $telUrl = $phoneInternational ? 'tel:+' . $phoneInternational : null;
    $mailUrl = $contact?->email ? 'mailto:' . $contact->email : null;
    $hasCoordinates = $contact && $contact->latitude !== null && $contact->longitude !== null;
    $mapQuery = $hasCoordinates
        ? $contact->latitude . ',' . $contact->longitude
        : ($contact?->address ?? null);
    $mapUrl = $mapQuery
        ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($mapQuery)
        : ($contact?->map_embed_url ?? null);
    $navItems = [
        ['label' => 'Beranda', 'route' => 'home'],
        ['label' => 'Profil', 'route' => 'profile'],
        ['label' => 'Paket Umrah', 'route' => 'packages'],
        ['label' => 'Jadwal', 'route' => 'schedules'],
        ['label' => 'Galeri', 'route' => 'galleries'],
        ['label' => 'Kontak', 'route' => 'contact'],
    ];
@endphp

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PT Amara Al Medina Travel')</title>
    <meta name="description" content="@yield('description', 'Informasi paket umrah, jadwal keberangkatan, galeri, profil, dan kontak PT Amara Al Medina Travel.')">
    <link rel="icon" href="{{ $faviconUrl }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <div class="container header-inner" data-public-nav>
            <a class="brand" href="{{ route('home') }}" aria-label="PT Amara Al Medina Travel">
                <img src="{{ $logoUrl }}" alt="PT Amara Al Medina Travel">
            </a>

            <nav class="site-nav" id="public-navigation" aria-label="Navigasi utama" data-public-nav-menu>
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}" @class(['active' => request()->routeIs($item['route']) || ($item['route'] === 'packages' && request()->routeIs('packages.*'))])>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="header-actions">
                <a class="btn btn-pink header-cta" href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener">
                    <x-heroicon-o-chat-bubble-left-right class="header-cta-icon" aria-hidden="true" />
                    <span class="header-cta-label">Hubungi Kami</span>
                </a>
                <button
                    class="nav-toggle"
                    type="button"
                    aria-controls="public-navigation"
                    aria-expanded="false"
                    aria-label="Buka menu navigasi"
                    data-public-nav-toggle
                >
                    <x-heroicon-o-bars-3 class="nav-toggle-open" aria-hidden="true" />
                    <x-heroicon-o-x-mark class="nav-toggle-close" aria-hidden="true" />
                </button>
            </div>
        </div>
    </header>

    <main data-motion-scope>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container footer-compact">
            <div class="footer-brand">
                <img class="footer-logo" src="{{ $logoUrl }}" alt="PT Amara Al Medina Travel">
                <p>Perjalanan ibadah umrah yang amanah, nyaman, dan terpercaya bersama tim profesional.</p>
            </div>
            @if ($contact?->address)
                <div class="footer-address">
                    <x-heroicon-o-map-pin class="footer-contact-icon" aria-hidden="true" />
                    <a class="footer-link" href="{{ $mapUrl }}" target="_blank" rel="noopener">{{ $contact->address }}</a>
                </div>
            @endif
            <div class="footer-direct">
                @if ($contact?->email)
                    <p class="footer-contact-item">
                        <x-heroicon-o-envelope class="footer-contact-icon" aria-hidden="true" />
                        <a class="footer-link" href="{{ $mailUrl }}">{{ $contact->email }}</a>
                    </p>
                @endif
                @if ($contact?->whatsapp)
                    <p class="footer-contact-item">
                        <x-heroicon-o-phone class="footer-contact-icon" aria-hidden="true" />
                        <a class="footer-link" href="{{ $telUrl }}">{{ $contact->whatsapp }}</a>
                    </p>
                @endif
            </div>
        </div>
        <div class="footer-bottom">
            © {{ date('Y') }} PT Amara Al Medina Travel
        </div>
    </footer>
</body>
</html>
