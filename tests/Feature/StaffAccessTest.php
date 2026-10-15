<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StaffAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_permissions_decide_which_sections_staff_can_open(): void
    {
        $warehouse = Team::factory()->withPermissions(['inventory'])->create();
        $this->actingAs(User::factory()->admin()->create(['team_id' => $warehouse->id]));

        $this->get('/admin/inventory')->assertOk();
        $this->get('/admin/orders')->assertForbidden();
        $this->get('/admin/products')->assertForbidden();
        $this->get('/admin/settings')->assertForbidden();
        $this->get('/admin/maintenance')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
    }

    public function test_staff_without_a_team_only_see_the_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/admin')->assertOk();
        $this->get('/admin/inventory')->assertForbidden();
    }

    public function test_super_admin_can_open_system_sections(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        foreach (['/admin/users', '/admin/users/create', '/admin/teams', '/admin/settings', '/admin/maintenance', '/admin/messages'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_super_admin_creates_a_staff_member_in_a_team(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $sales = Team::factory()->withPermissions(['orders'])->create();

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Wanjiru Sales',
                'email' => 'wanjiru@example.com',
                'password' => 'a-long-secure-password',
                'is_admin' => true,
                'team_id' => $sales->id,
                'is_super_admin' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::firstWhere('email', 'wanjiru@example.com');
        $this->assertTrue($user->is_admin);
        $this->assertTrue($user->hasPermission('orders'));
        $this->assertFalse($user->hasPermission('inventory'));
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_super_admin_cannot_remove_their_own_access(): void
    {
        $me = User::factory()->superAdmin()->create();
        $this->actingAs($me);

        Livewire::test(EditUser::class, ['record' => $me->id])
            ->fillForm(['name' => 'Renamed'])
            ->call('save')
            ->assertHasNoFormErrors();

        $me->refresh();
        $this->assertSame('Renamed', $me->name);
        $this->assertTrue($me->isSuperAdmin());
    }
}
