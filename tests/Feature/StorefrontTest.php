<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\SeoMeta;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CatalogSeeder::class);
    }

    public function test_home_page_shows_the_four_product_lines(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Paper Products')
            ->assertSee('Professional Chemical Care')
            ->assertSee('Institutional Hardware')
            ->assertSee('Aluminium Foil &amp; Food Packaging', false);
    }

    public function test_product_line_page_lists_products_from_its_sections(): void
    {
        $this->get('/products/paper-products')
            ->assertOk()
            ->assertSee('Softia Premium Toilet Tissue 6-Pack')
            ->assertSee('Softia Serviettes 1-Ply')
            ->assertDontSee('Softia Toilet Cleaner 750ml');
    }

    public function test_product_line_page_filters_by_section(): void
    {
        $this->get('/products/paper-products?section=serviettes')
            ->assertOk()
            ->assertSee('Softia Serviettes 1-Ply')
            ->assertDontSee('Softia Premium Toilet Tissue 6-Pack');
    }

    public function test_sections_are_not_reachable_as_product_lines(): void
    {
        $this->get('/products/serviettes')->assertNotFound();
    }

    public function test_product_page_requires_the_matching_product_line(): void
    {
        $this->get('/products/paper-products/softia-premium-toilet-tissue-6-pack')
            ->assertOk()
            ->assertSee('SFT-PP-101');

        $this->get('/products/chemical-care/softia-premium-toilet-tissue-6-pack')->assertNotFound();
    }

    public function test_seo_overrides_from_the_admin_are_rendered(): void
    {
        $product = Product::firstWhere('slug', 'softia-premium-toilet-tissue-6-pack');
        $product->seo()->create([
            'meta_title' => 'Buy Softia 6-Pack Tissue Online',
            'meta_description' => 'Soft 2-ply tissue delivered in Nairobi.',
            'canonical_url' => 'https://softia.co.ke/tissue',
        ]);

        $this->get('/products/paper-products/softia-premium-toilet-tissue-6-pack')
            ->assertSee('<title>Buy Softia 6-Pack Tissue Online</title>', false)
            ->assertSee('<link rel="canonical" href="https://softia.co.ke/tissue">', false);
    }

    public function test_route_level_seo_applies_to_the_home_page(): void
    {
        SeoMeta::factory()->forRoute('home')->create(['meta_title' => 'Softia Kenya Home']);

        $this->get('/')->assertSee('<title>Softia Kenya Home</title>', false);
    }

    public function test_non_production_sites_are_never_indexed(): void
    {
        SeoMeta::factory()->forRoute('home')->create(['robots' => 'index, follow']);

        $this->get('/')->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->app['env'] = 'production';
        $this->get('/')->assertSee('<meta name="robots" content="index, follow">', false);
    }

    public function test_inactive_product_lines_are_hidden(): void
    {
        Category::firstWhere('slug', 'institutional-hardware')->update(['is_active' => false]);

        $this->get('/')->assertDontSee('Institutional Hardware');
        $this->get('/products/institutional-hardware')->assertNotFound();
    }
}
