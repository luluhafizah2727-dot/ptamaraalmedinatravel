<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\Contact;
use App\Models\Gallery;
use App\Models\Schedule;
use App\Models\SiteSetting;
use App\Models\UmrahPackage;
use Illuminate\Contracts\View\View;

class PublicPageController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            ...$this->sharedData(),
            'featuredPackage' => UmrahPackage::query()
                ->where('is_active', true)
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->first(),
            'packages' => UmrahPackage::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->limit(3)
                ->get(),
            'schedules' => Schedule::query()
                ->with('umrahPackage')
                ->where('is_active', true)
                ->orderBy('departure_date')
                ->limit(5)
                ->get(),
            'galleries' => Gallery::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->limit(6)
                ->get(),
        ]);
    }

    public function profile(): View
    {
        return view('public.profile', $this->sharedData());
    }

    public function packages(): View
    {
        return view('public.packages', [
            ...$this->sharedData(),
            'packages' => UmrahPackage::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function packageDetail(UmrahPackage $package): View
    {
        abort_unless($package->is_active, 404);

        return view('public.package-detail', [
            ...$this->sharedData(),
            'package' => $package,
            'schedules' => $package->schedules()
                ->where('is_active', true)
                ->orderBy('departure_date')
                ->get(),
        ]);
    }

    public function schedules(): View
    {
        return view('public.schedules', [
            ...$this->sharedData(),
            'schedules' => Schedule::query()
                ->with('umrahPackage')
                ->where('is_active', true)
                ->orderBy('departure_date')
                ->get(),
        ]);
    }

    public function galleries(): View
    {
        return view('public.galleries', [
            ...$this->sharedData(),
            'galleries' => Gallery::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function contact(): View
    {
        return view('public.contact', $this->sharedData());
    }

    private function sharedData(): array
    {
        return [
            'profile' => CompanyProfile::query()->where('is_active', true)->first(),
            'contact' => Contact::query()->where('is_active', true)->first(),
            'settings' => SiteSetting::query()->pluck('value', 'key'),
            'siteMedia' => [
                'logo' => SiteSetting::mediaUrl(SiteSetting::BRAND_LOGO_KEY, 'images/site/logo.png'),
                'favicon' => SiteSetting::mediaUrl(SiteSetting::FAVICON_KEY, 'images/site/logo.png'),
                'hero' => SiteSetting::mediaUrl(SiteSetting::HERO_IMAGE_KEY, 'images/site/beranda-img.jpg'),
            ],
        ];
    }
}
