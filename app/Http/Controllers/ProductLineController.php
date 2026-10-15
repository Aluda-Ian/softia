<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductLineController extends Controller
{
    public function show(Request $request, Category $productLine): View
    {
        abort_unless($productLine->isProductLine() && $productLine->is_active, 404);

        $sections = $productLine->children()->where('is_active', true)->get();
        $activeSection = $sections->firstWhere('slug', $request->query('section'));

        $categoryIds = $activeSection
            ? [$activeSection->id]
            : $sections->pluck('id')->push($productLine->id)->all();

        $products = Product::query()
            ->active()
            ->whereIn('category_id', $categoryIds)
            ->with('category')
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('product-lines.show', [
            'productLine' => $productLine,
            'sections' => $sections,
            'activeSection' => $activeSection,
            'products' => $products,
            'seo' => Seo::forModel($productLine),
        ]);
    }
}
