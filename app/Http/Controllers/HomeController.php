<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\Seo;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'productLines' => Category::query()
                ->productLines()
                ->with('children:id,parent_id,name,slug')
                ->get(),
            'featuredProducts' => Product::query()
                ->active()
                ->where('is_featured', true)
                ->with('category.parent')
                ->latest('updated_at')
                ->take(4)
                ->get(),
            'seo' => Seo::forRoute('home'),
        ]);
    }
}
