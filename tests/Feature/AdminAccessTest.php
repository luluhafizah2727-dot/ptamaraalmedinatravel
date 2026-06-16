<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use App\Models\Contact;
use App\Models\Gallery;
use App\Models\Schedule;
use App\Models\SiteSetting;
use App\Models\UmrahPackage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_login_uses_branded_copy(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Login Admin')
            ->assertSee('Username')
            ->assertSee('Masukkan username');
    }

    public function test_admin_can_open_filament_dashboard(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Grafik Pengunjung');
    }

    public function test_admin_can_open_profile_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/profile')
            ->assertOk()
            ->assertSee('My Account')
            ->assertSee('Foto Avatar')
            ->assertSee('Kata sandi baru')
            ->assertSee('Konfirmasi Kata sandi baru')
            ->assertSee('Mode Terang')
            ->assertDontSee('admin-topbar-theme-switcher');
    }

    public function test_admin_can_open_resource_pages(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->create([
            'name' => 'Admin Resource',
            'email' => 'admin-resource@example.test',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $package = UmrahPackage::query()->firstOrFail();
        $schedule = Schedule::query()->firstOrFail();
        $gallery = Gallery::query()->firstOrFail();
        $companyProfile = CompanyProfile::query()->firstOrFail();
        $contact = Contact::query()->firstOrFail();
        $siteSetting = SiteSetting::query()->firstOrFail();

        $paths = [
            '/admin/umrah-packages',
            '/admin/umrah-packages/create',
            "/admin/umrah-packages/{$package->getRouteKey()}/edit",
            '/admin/schedules',
            '/admin/schedules/create',
            "/admin/schedules/{$schedule->getRouteKey()}/edit",
            '/admin/galleries',
            '/admin/galleries/create',
            "/admin/galleries/{$gallery->getRouteKey()}/edit",
            '/admin/company-profiles',
            '/admin/company-profiles/create',
            "/admin/company-profiles/{$companyProfile->getRouteKey()}/edit",
            '/admin/contacts',
            '/admin/contacts/create',
            "/admin/contacts/{$contact->getRouteKey()}/edit",
            '/admin/site-settings',
            '/admin/site-settings/create',
            "/admin/site-settings/{$siteSetting->getRouteKey()}/edit",
        ];

        foreach ($paths as $path) {
            $response = $this->actingAs($admin)->get($path);

            $this->assertSame(200, $response->baseResponse->getStatusCode(), "Admin resource page failed: {$path}");
        }
    }

    public function test_user_avatar_uses_public_storage_url(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'avatar_path' => 'avatars/admin.jpg',
            'is_admin' => true,
        ]);

        $this->assertStringContainsString('/storage/avatars/admin.jpg', $admin->getFilamentAvatarUrl());
    }
}
