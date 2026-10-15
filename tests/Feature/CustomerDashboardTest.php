<?php

namespace Tests\Feature;

use App\Enums\CustomerType;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_and_register_pages(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Welcome back');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create your account');
    }

    public function test_customer_can_register_and_past_orders_are_claimed(): void
    {
        // Existing order placed with this email as guest
        $pastOrder = Order::create([
            'reference' => 'SO-261015-TEST1',
            'type' => OrderType::Order,
            'status' => OrderStatus::Processing,
            'customer_type' => CustomerType::Retail,
            'customer_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '0712345678',
            'delivery_address' => 'Nairobi Kilimani',
            'payment_method' => PaymentMethod::Mpesa,
            'payment_status' => PaymentStatus::Paid,
            'subtotal' => 2400,
            'total' => 2400,
        ]);

        $response = $this->post(route('register.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '0712345678',
            'customer_type' => 'b2c',
            'delivery_address' => 'Nairobi Kilimani',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'jane@example.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->is_admin);

        // Verify past order was claimed and linked
        $this->assertEquals($user->id, $pastOrder->fresh()->user_id);
    }

    public function test_customer_can_login_and_access_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Alex Kimani',
            'email' => 'alex@example.com',
            'password' => 'Password123!',
            'is_admin' => false,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'alex@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Hello,')
            ->assertSee('Alex Kimani');
    }

    public function test_customer_can_track_their_orders(): void
    {
        $user = User::factory()->create([
            'email' => 'tracker@example.com',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'reference' => 'SO-261015-TRK99',
            'type' => OrderType::Order,
            'status' => OrderStatus::Dispatched,
            'customer_type' => CustomerType::Retail,
            'customer_name' => 'Tracker Customer',
            'email' => 'tracker@example.com',
            'phone' => '0722000000',
            'delivery_address' => 'Westlands Nairobi',
            'payment_method' => PaymentMethod::PayOnDelivery,
            'payment_status' => PaymentStatus::Unpaid,
            'subtotal' => 1500,
            'total' => 1500,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.orders'))
            ->assertOk()
            ->assertSee('SO-261015-TRK99')
            ->assertSee('Dispatched');

        $this->actingAs($user)
            ->get(route('dashboard.orders.show', $order))
            ->assertOk()
            ->assertSee('Live Order Progression')
            ->assertSee('Out for Delivery')
            ->assertSee('Westlands Nairobi');
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $userA = User::factory()->create(['email' => 'usera@example.com']);
        $userB = User::factory()->create(['email' => 'userb@example.com', 'is_admin' => false]);

        $orderA = Order::create([
            'user_id' => $userA->id,
            'reference' => 'SO-261015-PRIVATE',
            'type' => OrderType::Order,
            'status' => OrderStatus::Processing,
            'customer_type' => CustomerType::Retail,
            'customer_name' => 'User A',
            'email' => 'usera@example.com',
            'phone' => '0700000001',
            'delivery_address' => 'Private Address',
            'payment_method' => PaymentMethod::PayOnDelivery,
            'payment_status' => PaymentStatus::Unpaid,
            'subtotal' => 500,
            'total' => 500,
        ]);

        $this->actingAs($userB)
            ->get(route('dashboard.orders.show', $orderA))
            ->assertForbidden();
    }

    public function test_customer_can_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'update@example.com',
            'phone' => '0711111111',
            'delivery_address' => 'Old Address',
        ]);

        $this->actingAs($user)
            ->patch(route('dashboard.profile.update'), [
                'name' => 'New Name',
                'email' => 'update@example.com',
                'phone' => '0799999999',
                'customer_type' => 'b2b',
                'company_name' => 'Softia Partner Ltd',
                'kra_pin' => 'P051234567Z',
                'delivery_address' => 'New Building, Suite 4B',
            ])
            ->assertSessionHas('status');

        $fresh = $user->fresh();
        $this->assertEquals('New Name', $fresh->name);
        $this->assertEquals('0799999999', $fresh->phone);
        $this->assertEquals('Softia Partner Ltd', $fresh->company_name);
        $this->assertEquals('New Building, Suite 4B', $fresh->delivery_address);
    }
}
