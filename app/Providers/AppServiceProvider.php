<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Page;
use App\Support\Cart;
use App\Support\Settings\SettingsRegistry;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(Cart::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        SettingsRegistry::applyToConfig();

        // Allow admin uploads of update packages (PHP upload limits still apply).
        config(['livewire.temporary_file_upload.rules' => ['required', 'file', 'max:'.(150 * 1024)]]);

        View::composer('layouts.app', function (ViewInstance $view): void {
            $view->with([
                'cartCount' => app(Cart::class)->count(),
                'navProductLines' => Category::query()->productLines()->get(),
                // rescue(): the footer must never take the site down (e.g. before migrations run).
                'footerCompanyPages' => rescue(fn () => Page::query()->inFooter('company')->get(['title', 'slug']), collect(), report: false),
                'footerPolicyPages' => rescue(fn () => Page::query()->inFooter('policies')->get(['title', 'slug']), collect(), report: false),
            ]);
        });
    }
}
