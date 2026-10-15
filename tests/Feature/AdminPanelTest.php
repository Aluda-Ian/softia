<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Filament\Pages\Maintenance;
use App\Filament\Resources\Inventory\Pages\ManageInventory;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\SeoMetas\Pages\CreateSeoMeta;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\SeoMeta;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_users_cannot_access_the_panel(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_open_every_section(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $product = Product::factory()->create();
        $order = Order::factory()->quote()->create();

        foreach ([
            '/admin', '/admin/categories', '/admin/products', '/admin/inventory', '/admin/orders',
            '/admin/seo', '/admin/seo/create', '/admin/maintenance', '/admin/products/create',
            "/admin/products/{$product->slug}/edit", "/admin/orders/{$order->reference}",
            "/admin/orders/{$order->reference}/edit",
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_admin_can_create_a_product_with_seo_overrides(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $section = Category::factory()->sectionOf(Category::factory()->create())->create();

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Softia Hand Towel',
                'slug' => 'softia-hand-towel',
                'sku' => 'SFT-HT-1',
                'category_id' => $section->id,
                'retail_price' => 300,
                'min_wholesale_qty' => 10,
                'stock' => 40,
                'low_stock_threshold' => 5,
                'seo.meta_title' => 'Hand Towels Nairobi',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::firstWhere('slug', 'softia-hand-towel');
        $this->assertSame('Hand Towels Nairobi', $product->seo->meta_title);
    }

    public function test_saving_a_product_without_seo_overrides_creates_no_seo_record(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $product = Product::factory()->create();

        Livewire::test(EditProduct::class, ['record' => $product->slug])
            ->fillForm(['stock' => 12])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(12, $product->fresh()->stock);
        $this->assertSame(0, SeoMeta::count());
    }

    public function test_pricing_a_quote_recalculates_totals_and_marks_it_quoted(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $order = Order::factory()->quote()->create(['subtotal' => 0, 'total' => 0]);
        $item = $order->items()->create(['product_name' => 'Jumbo Roll', 'sku' => 'JR', 'unit_price' => null, 'quantity' => 4]);

        Livewire::test(EditOrder::class, ['record' => $order->reference])
            ->fillForm([
                'items' => [
                    "record-{$item->id}" => ['product_name' => 'Jumbo Roll', 'quantity' => 4, 'unit_price' => 3600],
                ],
                'delivery_fee' => 500,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $order->refresh();
        $this->assertSame('14400.00', $order->subtotal);
        $this->assertSame('14900.00', $order->total);
        $this->assertSame(OrderStatus::Quoted, $order->status);
    }

    public function test_inventory_adjustment_adds_received_stock(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $product = Product::factory()->create(['stock' => 5]);

        Livewire::test(ManageInventory::class)
            ->callAction(TestAction::make('adjust')->table($product), ['mode' => 'add', 'quantity' => 20])
            ->assertHasNoFormErrors();

        $this->assertSame(25, $product->fresh()->stock);
    }

    public function test_seo_manager_creates_a_page_entry(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test(CreateSeoMeta::class)
            ->fillForm([
                'target' => 'page',
                'route_name' => 'home',
                'meta_title' => 'Softia: Tissue & Hygiene Supplies Kenya',
                'robots' => 'index, follow',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('seo_metas', ['route_name' => 'home', 'seoable_type' => null]);
        $this->get('/')->assertSee('<title>Softia: Tissue &amp; Hygiene Supplies Kenya</title>', false);
    }

    public function test_maintenance_page_runs_a_whitelisted_task(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test(Maintenance::class)
            ->callAction(TestAction::make('runTask')->arguments(['task' => 'optimize-clear']))
            ->assertNotified()
            ->assertSet('lastResult.ok', true);
    }
}
