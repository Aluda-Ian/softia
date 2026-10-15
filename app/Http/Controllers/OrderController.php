<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\Seo;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Confirmation page; the route is signed so references cannot be guessed.
     */
    public function show(Order $order): View
    {
        return view('orders.show', [
            'order' => $order->load('items'),
            'seo' => new Seo("Order {$order->reference} | Softia", 'Your Softia order confirmation.', null, url()->current(), 'noindex, nofollow'),
        ]);
    }
}
