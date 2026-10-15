<?php

namespace App\Http\Controllers;

use App\Enums\CustomerType;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Support\Cart;
use App\Support\CartLine;
use App\Support\PaymentOptions;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly Cart $cart) {}

    public function create(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return to_route('cart.index');
        }

        $user = auth()->user();

        return view('checkout.create', [
            'lines' => $this->cart->lines(),
            'subtotal' => $this->cart->subtotal(),
            'hasQuoteOnlyItems' => $this->cart->hasQuoteOnlyItems() || ! PaymentOptions::retailCheckoutAvailable(),
            'paymentMethods' => PaymentOptions::retail(),
            'user' => $user,
            'seo' => new Seo('Checkout | Softia', 'Complete your Softia order.', null, url()->current(), 'noindex, nofollow'),
        ]);
    }

    /**
     * Businesses, and any cart with quote-only items, become a quotation
     * request. Individuals with fully priced carts place a retail order.
     */
    public function store(CheckoutRequest $request): RedirectResponse
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            return to_route('cart.index');
        }

        $data = $request->validated();
        $customerType = CustomerType::from($data['customer_type']);
        $isQuote = $customerType === CustomerType::Business
            || $this->cart->hasQuoteOnlyItems()
            || ! PaymentOptions::retailCheckoutAvailable();

        $paymentMethod = $isQuote ? PaymentMethod::Invoice : PaymentMethod::from($data['payment_method']);
        $paymentReference = $paymentMethod === PaymentMethod::Mpesa ? strtoupper(trim($data['payment_reference'])) : null;
        $subtotal = $this->cart->subtotal();
        $user = $request->user();

        $order = DB::transaction(function () use ($data, $customerType, $isQuote, $paymentMethod, $paymentReference, $subtotal, $lines, $user): Order {
            $order = Order::create([
                'user_id' => $user?->id,
                'type' => $isQuote ? OrderType::Quote : OrderType::Order,
                'status' => OrderStatus::Pending,
                'customer_type' => $customerType,
                'customer_name' => $data['customer_name'],
                'company_name' => $data['company_name'] ?? null,
                'kra_pin' => $data['kra_pin'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'],
                'delivery_address' => $data['delivery_address'],
                'notes' => $data['notes'] ?? null,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference,
                'payment_status' => $paymentReference ? PaymentStatus::AwaitingVerification : PaymentStatus::Unpaid,
                'subtotal' => $subtotal,
                'delivery_fee' => 0,
                'total' => $subtotal,
            ]);

            $order->items()->createMany($lines->map(fn (CartLine $line): array => [
                'product_id' => $line->product->id,
                'product_name' => $line->product->name,
                'sku' => $line->product->sku,
                'unit_price' => $line->unitPrice(),
                'quantity' => $line->quantity,
                'line_total' => $line->lineTotal(),
            ])->all());

            return $order;
        });

        $this->cart->clear();

        return redirect(URL::signedRoute('orders.show', $order));
    }
}
