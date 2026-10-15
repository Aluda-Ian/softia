<?php

namespace Tests\Feature;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContentPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_lists_company_and_policy_pages_with_news(): void
    {
        $this->seed(ContentSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Company', 'About Us', 'News', 'Contact Us', 'Become a Distributor'])
            ->assertSeeInOrder(['Policies', 'Privacy Policy', 'Terms &amp; Conditions', 'Delivery Policy', 'Returns &amp; Refunds'], false);
    }

    public function test_published_page_renders_its_content(): void
    {
        $page = Page::factory()->inFooter('company')->create(['title' => 'Our Story', 'body' => '<h2>Since 2020</h2><p>Made in Kenya.</p>']);

        $this->get("/pages/{$page->slug}")
            ->assertOk()
            ->assertSee('<title>Our Story | Softia</title>', false)
            ->assertSee('<h2>Since 2020</h2>', false);
    }

    public function test_page_content_is_sanitised(): void
    {
        $page = Page::factory()->create(['body' => '<p>Safe</p><script>alert(1)</script>']);

        $this->get("/pages/{$page->slug}")
            ->assertSee('<p>Safe</p>', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_unpublished_pages_are_hidden_everywhere(): void
    {
        $page = Page::factory()->draft()->inFooter('policies')->create(['title' => 'Secret Draft']);

        $this->get("/pages/{$page->slug}")->assertNotFound();
        $this->get('/')->assertDontSee('Secret Draft');
    }

    public function test_news_lists_only_published_articles(): void
    {
        Post::factory()->create(['title' => 'Live Article']);
        Post::factory()->draft()->create(['title' => 'Draft Article']);
        $scheduled = Post::factory()->scheduled()->create(['title' => 'Future Article']);

        $this->get('/news')
            ->assertOk()
            ->assertSee('Live Article')
            ->assertDontSee('Draft Article')
            ->assertDontSee('Future Article');

        $this->get("/news/{$scheduled->slug}")->assertNotFound();
    }

    public function test_news_article_page_renders(): void
    {
        $post = Post::factory()->create(['title' => 'New Jumbo Rolls', 'excerpt' => 'Bigger rolls for busy washrooms.']);

        $this->get("/news/{$post->slug}")
            ->assertOk()
            ->assertSee('New Jumbo Rolls')
            ->assertSee('<meta name="description" content="Bigger rolls for busy washrooms.">', false);
    }

    public function test_admin_can_manage_pages_and_news(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $page = Page::factory()->create();
        $post = Post::factory()->create();

        foreach (['/admin/pages', '/admin/pages/create', "/admin/pages/{$page->slug}/edit", '/admin/news', '/admin/news/create', "/admin/news/{$post->slug}/edit"] as $url) {
            $this->get($url)->assertOk();
        }

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Softia at the Hospitality Expo',
                'slug' => 'softia-at-the-hospitality-expo',
                'excerpt' => 'Meet us at stand 12.',
                'body' => '<p>See you there.</p>',
                'published_at' => now()->subHour(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->get('/news')->assertSee('Softia at the Hospitality Expo');
    }
}
