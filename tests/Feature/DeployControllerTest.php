<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeployControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = str_repeat('a', 48);
        config(['deploy.token' => $this->token]);
    }

    public function test_runner_is_hidden_when_no_token_is_configured(): void
    {
        config(['deploy.token' => null]);

        $this->get('/_deploy/anything')->assertNotFound();
    }

    public function test_runner_rejects_a_wrong_token(): void
    {
        $this->get('/_deploy/'.str_repeat('b', 48))->assertNotFound();
    }

    public function test_runner_page_loads_with_the_correct_token(): void
    {
        $this->get("/_deploy/{$this->token}")
            ->assertOk()
            ->assertSee('Run database migrations')
            ->assertSee('Create the first admin account');
    }

    public function test_whitelisted_task_runs(): void
    {
        $this->post("/_deploy/{$this->token}/run", ['task' => 'optimize-clear'])
            ->assertRedirect()
            ->assertSessionHas('result.ok', true);
    }

    public function test_unknown_task_is_rejected(): void
    {
        $this->post("/_deploy/{$this->token}/run", ['task' => 'db:wipe'])
            ->assertSessionHasErrors('task');
    }

    public function test_first_admin_can_be_created_only_once(): void
    {
        $payload = [
            'name' => 'Softia Admin',
            'email' => 'admin@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ];

        $this->post("/_deploy/{$this->token}/admin", $payload)->assertRedirect();

        $this->assertTrue(User::firstWhere('email', 'admin@example.com')->is_admin);

        $this->post("/_deploy/{$this->token}/admin", ['email' => 'other@example.com'] + $payload)
            ->assertForbidden();
    }
}
