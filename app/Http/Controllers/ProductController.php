<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\Seo;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Category $productLine, Product $product): View
    {
        abort_unless($product->is_active && $product->productLine()->is($productLine), 404);

        return view('products.show', [
            'productLine' => $productLine,
            'product' => $product,
            'related' => Product::query()
                ->active()
                ->where('category_id', $product->category_id)
                ->whereKeyNot($product->id)
                ->take(4)
                ->get(),
            'seo' => Seo::forModel($product),
        ]);
    }
}
