<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\Seo;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        return view('news.index', [
            'posts' => Post::query()->published()->latest('published_at')->paginate(9),
            'seo' => Seo::forRoute('news.index', 'News | Softia', 'Product launches, company updates and hygiene tips from Softia.'),
        ]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->isPublished(), 404);

        return view('news.show', [
            'post' => $post,
            'morePosts' => Post::query()->published()->whereKeyNot($post->id)->latest('published_at')->take(3)->get(),
            'seo' => Seo::forModel($post),
        ]);
    }
}
