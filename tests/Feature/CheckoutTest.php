<?php

namespace Tests\Feature;

use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function customer(array $overrides = []): array
    {
        return $overrides + [
            'customer_type' => 'b2c',
            'customer_name' => 'Jane Wanjiku',
            'email' => 'jane@example.com',
            'phone' => '0712345678',
            'delivery_address' => 'Kilimani, Nairobi',
            'payment_method' => 'pay_on_delivery',
        ];
    }

    public function test_adding_to_cart_applies_wholesale_price_at_minimum_quantity(): void
    {
        $product = Product::factory()->create(['retail_price' => 100, 'wholesale_price' => 80, 'min_wholesale_qty' => 10]);

        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 9]);
        $this->get('/cart')->assertSee('KES 900');

        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 1]);
        $this->get('/cart')->assertSee('KES 800')->assertSee('Wholesale');
    }

    public function test_individual_places_a_pay_on_delivery_order(): void
    {
        $product = Product::factory()->create(['retail_price' => 250, 'wholesale_price' => null]);
        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 2]);

        $this->post('/checkout', $this->customer())->assertRedirect();

        $order = Order::with('items')->sole();
        $this->assertSame(OrderType::Order, $order->type);
        $this->assertSame(PaymentMethod::PayOnDelivery, $order->payment_method);
        $this->assertSame('500.00', $order->total);
        $this->assertSame($product->name, $order->items->sole()->product_name);
        $this->assertStringStartsWith('SO-', $order->reference);

        $this->get('/cart')->assertSee('Your cart is empty');
    }

    public function test_mpesa_order_requires_and_stores_the_confirmation_code(): void
    {
        $product = Product::factory()->create();
        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 1]);

        $this->post('/checkout', $this->customer(['payment_method' => 'mpesa']))
            ->assertSessionHasErrors('payment_reference');

        $this->post('/checkout', $this->customer(['payment_method' => 'mpesa', 'payment_reference' => 'sjk4abc123']));

        $order = Order::sole();
        $this->assertSame('SJK4ABC123', $order->payment_reference);
        $this->assertSame(PaymentStatus::AwaitingVerification, $order->payment_status);
    }

    public function test_business_checkout_creates_a_quotation_request(): void
    {
        $product = Product::factory()->create();
        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 50]);

        $this->post('/checkout', $this->customer(['customer_type' => 'b2b']))
            ->assertSessionHasErrors('company_name');

        $this->post('/checkout', $this->customer(['customer_type' => 'b2b', 'company_name' => 'Nairobi Hospital']))
            ->assertRedirect();

        $order = Order::sole();
        $this->assertSame(OrderType::Quote, $order->type);
        $this->assertSame(PaymentMethod::Invoice, $order->payment_method);
        $this->assertStringStartsWith('QT-', $order->reference);
    }

    public function test_quote_only_items_turn_a_retail_cart_into_a_quote_without_payment(): void
    {
        $product = Product::factory()->quoteOnly()->create();
        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 5]);

        $this->post('/checkout', $this->customer(['payment_method' => null]))->assertRedirect();

        $this->assertSame(OrderType::Quote, Order::sole()->type);
    }

    public function test_order_confirmation_requires_a_signed_link(): void
    {
        $order = Order::factory()->create();

        $this->get("/orders/{$order->reference}")->assertForbidden();
    }

    public function test_checkout_redirects_when_cart_is_empty(): void
    {
        $this->get('/checkout')->assertRedirect('/cart');
    }
}
