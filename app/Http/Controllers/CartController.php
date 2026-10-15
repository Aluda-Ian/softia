<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Cart;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly Cart $cart) {}

    public function index(): View
    {
        return view('cart.index', [
            'lines' => $this->cart->lines(),
            'subtotal' => $this->cart->subtotal(),
            'hasQuoteOnlyItems' => $this->cart->hasQuoteOnlyItems(),
            'seo' => Seo::forRoute('cart', 'Your Cart | Softia'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);

        $product = Product::query()->active()->findOrFail($validated['product_id']);

        $this->cart->add($product, $validated['quantity']);

        return back()->with('status', "{$product->name} added to your cart.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        $this->cart->update($product, $validated['quantity']);

        return to_route('cart.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->cart->remove($product);

        return to_route('cart.index')->with('status', "{$product->name} removed.");
    }
}
