<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Peter Otieno',
            'email' => 'peter@example.com',
            'phone' => '0712345678',
            'company' => 'Lakeview Hotel',
            'topic' => 'quotation',
            'message' => 'We need 200 jumbo rolls every month.',
        ];
    }

    public function test_contact_page_renders_the_form(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('Send message')
            ->assertSee(config('shop.contact.email'));
    }

    public function test_message_is_saved_and_emailed_to_sales(): void
    {
        Mail::fake();

        $this->post('/contact', $this->payload())
            ->assertRedirect('/contact')
            ->assertSessionHas('contact_sent');

        $message = ContactMessage::sole();
        $this->assertSame('Lakeview Hotel', $message->company);
        $this->assertNull($message->read_at);

        Mail::assertSent(ContactMessageReceived::class, fn (ContactMessageReceived $mail) => $mail->hasTo(config('shop.contact.email'))
            && $mail->hasReplyTo('peter@example.com'));
    }

    public function test_honeypot_blocks_bots(): void
    {
        Mail::fake();

        $this->post('/contact', $this->payload(['website' => 'http://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertSame(0, ContactMessage::count());
        Mail::assertNothingSent();
    }

    public function test_old_contact_page_redirects_to_the_form(): void
    {
        $this->get('/pages/contact-us')->assertRedirect('/contact');
    }

    public function test_opening_a_message_in_the_admin_marks_it_read(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $message = ContactMessage::factory()->create();

        $this->get('/admin/messages')->assertOk()->assertSee($message->name);
        $this->get("/admin/messages/{$message->id}")->assertOk()->assertSee($message->message);

        $this->assertNotNull($message->fresh()->read_at);
    }
}
