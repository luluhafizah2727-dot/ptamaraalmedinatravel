<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\UmrahPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_public_pages_render_seeded_content(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Perjalanan Ibadah Umrah')
            ->assertSee('Lihat Jadwal')
            ->assertSee('storage/site-settings/brand-logo.png')
            ->assertSee('storage/site-settings/favicon.png')
            ->assertDontSee('images/site/logo-nobg.png')
            ->assertSee('storage/site-settings/hero-image.jpg');
        $this->get('/profil')
            ->assertOk()
            ->assertSee('Profil Perusahaan')
            ->assertSee('Mengenal layanan dan komitmen PT Amara Al Medina Travel.')
            ->assertDontSee('Home / Profil');
        $this->get('/paket-umrah')->assertOk()->assertSee('Umroh Plus Tarim Paket 19 Hari');
        $this->get('/jadwal')->assertOk()->assertSee('Jadwal Keberangkatan');
        $this->get('/galeri')
            ->assertOk()
            ->assertSee('Galeri Kegiatan')
            ->assertSee('data-gallery-lightbox', false)
            ->assertSee('data-gallery-trigger', false);
        $this->get('/kontak')
            ->assertOk()
            ->assertSee('Informasi Kontak')
            ->assertSee('Hubungi admin untuk konsultasi paket, jadwal, dan keberangkatan.')
            ->assertSee('href="tel:+6282252239507"', false)
            ->assertSee('href="mailto:ptamaraalmedinatravel@gmail.com"', false)
            ->assertSee('https://www.google.com/maps/search/?api=1', false)
            ->assertDontSee('Home / Kontak');
    }

    public function test_package_detail_route_uses_slug(): void
    {
        $package = UmrahPackage::query()->firstOrFail();

        $this->get(route('packages.show', $package))
            ->assertOk()
            ->assertSee($package->name);
    }

    public function test_content_models_can_be_updated_for_crud_flow(): void
    {
        $package = UmrahPackage::query()->firstOrFail();
        $package->update(['price' => 62000000]);

        $gallery = Gallery::query()->create([
            'title' => 'Test Upload Foto',
            'image_path' => 'galleries/test.jpeg',
            'taken_at' => '2026-06-14',
            'is_active' => true,
            'sort_order' => 99,
        ]);

        $this->assertDatabaseHas('umrah_packages', [
            'id' => $package->id,
            'price' => 62000000,
        ]);
        $this->assertDatabaseHas('galleries', ['id' => $gallery->id]);

        $gallery->delete();

        $this->assertDatabaseMissing('galleries', ['id' => $gallery->id]);
    }
}
