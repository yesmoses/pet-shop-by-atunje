<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can register.
     *
     * @return void
     */
    public function test_admin_registration(): void
    {
        $user = User::factory()->make()->toArray();
        $user['password'] = 'password1';
        $user['password_confirmation'] = 'password1';
        $user['is_marketing'] = 'is_marketing';

        $response = $this->post(route('admin.create'), $user);

        $response->assertStatus(200);

        //check if access token was created
        $content = json_decode($response->content(), true);
        $data = $content['data'];
        $this->assertArrayHasKey('token', $data);
    }

    public function test_admin_can_login()
    {
        //create admin user with default password - password
        $user = User::factory()->admin()->create();

        $response = $this->post(route('admin.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);

        //check if access token was created
        $content = json_decode($response->content(), true);
        $data = $content['data'];
        $this->assertArrayHasKey('token', $data);
    }

    public function test_user_cannot_login_on_admin_route()
    {
        //create user with default password - password
        $user = User::factory()->create();

        $response = $this->post(route('admin.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_admin_cannot_login_with_invalid_credentials()
    {
        //create user with default password - password
        $user = User::factory()->admin()->create();

        $response = $this->post(route('admin.login'), [
            'email' => $user->email,
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_admin_can_logout()
    {
        $response = $this->get(route('admin.logout'), $this->getAdminAuthHeaders());
        $response->assertStatus(200);
    }

    public function test_admin_account_cannot_be_edited()
    {
        $user = User::factory()->admin()->create();
        $user_arr = $user->toArray();

        $updated = User::factory()->admin_marketing()->make()->toArray();
        $updated['is_marketing'] = 'is_marketing';
        $updated['password'] = 'password';
        $updated['password_confirmation'] = 'password';
        $updated = array_merge($user_arr, $updated);

        $response = $this->put(route('user.update'), $updated, $this->getAdminAuthHeaders($user));
        $response->assertStatus(401);

        $user->refresh();
        $this->assertNotEquals($user->first_name, $updated['first_name']);
    }

    public function test_admin_account_cannot_be_deleted()
    {
        $response = $this->delete(route('user.delete'), [], $this->getAdminAuthHeaders());
        $response->assertStatus(401);
    }

    public function test_admin_can_view_user_listing()
    {
        $response = $this->post(route('admin.user-listing'), [], $this->getAdminAuthHeaders());
        $response->assertStatus(200)
            //confirm if record is paginated
            ->assertJsonPath('data.current_page', 1);
    }

    public function test_admin_can_edit_user()
    {
        $user = User::factory()->create();
        $user_arr = $user->toArray();

        $updated = User::factory()->marketing()->make()->toArray();
        $updated['is_marketing'] = 'is_marketing';
        $updated['password'] = 'password';
        $updated['password_confirmation'] = 'password';
        $updated = array_merge($user_arr, $updated);

        $response = $this->put(route('admin.user-update', ['user' => $user->uuid]), $updated, $this->getAdminAuthHeaders());
        $response->assertStatus(Response::HTTP_OK);

        $user->refresh();
        $this->assertEquals($user->first_name, $updated['first_name']);
    }

    public function test_admin_can_delete_user_account()
    {
        $this->withoutExceptionHandling();

        //create user
        $user = User::factory()->create();

        //delete the user with admin account
        $response = $this->delete(route('admin.user-delete', ['user' => $user->uuid]), [], $this->getAdminAuthHeaders());
        $response->assertStatus(200);

        //fetch the user afresh from the db
        $user = User::find($user->id);
        $this->assertNull($user);
    }
}
