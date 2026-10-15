<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\Product;
use App\Support\Cart;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $ordersQuery = Order::query()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('email', $user->email);
            })
            ->latest();

        $totalOrders = (clone $ordersQuery)->count();
        $activeOrders = (clone $ordersQuery)
            ->whereIn('status', [
                OrderStatus::Pending,
                OrderStatus::Quoted,
                OrderStatus::Confirmed,
                OrderStatus::Processing,
                OrderStatus::Dispatched,
            ])
            ->count();
        $completedOrders = (clone $ordersQuery)->where('status', OrderStatus::Completed)->count();
        $quotesCount = (clone $ordersQuery)->where('type', OrderType::Quote)->count();

        $recentOrders = (clone $ordersQuery)->with('items')->take(5)->get();

        // The single most recent active order to highlight with real-time tracking
        $activeTrackingOrder = (clone $ordersQuery)
            ->with('items')
            ->whereIn('status', [
                OrderStatus::Pending,
                OrderStatus::Confirmed,
                OrderStatus::Processing,
                OrderStatus::Dispatched,
            ])
            ->first();

        return view('dashboard.index', [
            'user' => $user,
            'totalOrders' => $totalOrders,
            'activeOrders' => $activeOrders,
            'completedOrders' => $completedOrders,
            'quotesCount' => $quotesCount,
            'recentOrders' => $recentOrders,
            'activeTrackingOrder' => $activeTrackingOrder,
            'seo' => new Seo('My Dashboard · Softia', 'Manage your Softia orders and deliveries.', null, url()->current(), 'noindex, nofollow'),
        ]);
    }

    public function orders(Request $request): View
    {
        $user = $request->user();
        $filter = $request->string('tab', 'all')->toString();
        $search = $request->string('q')->trim()->toString();

        $query = Order::query()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('email', $user->email);
            })
            ->with('items')
            ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('items', function ($iq) use ($search) {
                      $iq->where('product_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($filter === 'active') {
            $query->whereIn('status', [
                OrderStatus::Pending,
                OrderStatus::Quoted,
                OrderStatus::Confirmed,
                OrderStatus::Processing,
                OrderStatus::Dispatched,
            ]);
        } elseif ($filter === 'completed') {
            $query->where('status', OrderStatus::Completed);
        } elseif ($filter === 'quotes') {
            $query->where('type', OrderType::Quote);
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('dashboard.orders.index', [
            'user' => $user,
            'orders' => $orders,
            'currentTab' => $filter,
            'search' => $search,
            'seo' => new Seo('My Orders · Softia', 'View and track all your Softia orders and quotations.', null, url()->current(), 'noindex, nofollow'),
        ]);
    }

    public function showOrder(Request $request, Order $order): View
    {
        $this->authorizeOrder($request, $order);

        $order->load('items');

        return view('dashboard.orders.show', [
            'user' => $request->user(),
            'order' => $order,
            'seo' => new Seo("Track Order {$order->reference} · Softia", 'Live order status and tracking details.', null, url()->current(), 'noindex, nofollow'),
        ]);
    }

    public function reorder(Request $request, Order $order, Cart $cart): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        $order->load('items');
        $addedCount = 0;

        foreach ($order->items as $item) {
            $product = $item->product_id ? Product::find($item->product_id) : null;
            if ($product && $product->is_active) {
                $cart->add($product, $item->quantity);
                $addedCount++;
            }
        }

        if ($addedCount === 0) {
            return back()->with('status', 'None of the items from this order are currently available for retail cart.');
        }

        return redirect()->route('cart.index')->with('status', "Added {$addedCount} item(s) from order {$order->reference} into your cart.");
    }

    public function profile(Request $request): View
    {
        return view('dashboard.profile', [
            'user' => $request->user(),
            'seo' => new Seo('My Profile · Softia', 'Update your contact details and default delivery address.', null, url()->current(), 'noindex, nofollow'),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:30'],
            'customer_type' => ['required', 'string', 'in:b2c,b2b'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'kra_pin' => ['nullable', 'string', 'max:20'],
            'delivery_address' => ['nullable', 'string', 'max:1000'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'customer_type' => $validated['customer_type'],
            'company_name' => $validated['company_name'] ?? null,
            'kra_pin' => $validated['kra_pin'] ?? null,
            'delivery_address' => $validated['delivery_address'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        return back()->with('status', 'Your profile and delivery details have been updated.');
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        $user = $request->user();

        $allowed = ($order->user_id && $order->user_id === $user->id)
            || ($order->email === $user->email)
            || $user->is_admin;

        abort_unless($allowed, 403, 'You are not authorized to view this order.');
    }
}
