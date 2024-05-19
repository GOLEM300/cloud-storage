<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * post /auth/register
     * @return void
     */
    public function testRegisterUser()
    {
        $user = [
            'name' => 'test_user',
            'email' => 'test@example.com',
            'password' => '12345678',
        ];

        $response = $this->postJson('/api/v1/auth/register', $user);

        $response->assertStatus(201)->assertJsonStructure(['message', 'email', 'token']);

        $this->assertDatabaseHas('users', [
            'name' => 'test_user',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * post /auth/login
     * @return void
     */
    public function testLoginUser()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $login_data = [
            'email' => 'test@example.com',
            'password' => '12345678',
        ];


        $response = $this->postJson('/api/v1/auth/login', $login_data);
        $response->assertStatus(200)->assertJsonStructure(['message', 'email', 'token',]);
    }

    /**
     * post /auth/logout
     * @return void
     */
    public function testLogoutUser()
    {
        $user = User::factory()->create();

        $token = $user->createToken('Test Token')->plainTextToken;

        $response = $this->actingAs($user)->get('/api/v1/auth/logout');

        $response->assertStatus(200)->assertJson(['message' => 'Пользователь разлогинен']);

        $this->assertEmpty($user->tokens);
    }
}
