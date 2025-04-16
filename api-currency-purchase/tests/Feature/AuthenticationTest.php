<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--force' => true]);
    }
    /**
     * Usuário pode se registrar
     */
    public function test_users_can_register(): void
    {
        $response = $this->postJson(
            '/api/auth/register',
            [
                'name' => 'User Test',
                'email' => 'user@gestaoclick.com.br',
                'password' => 'password#123',
                'password_confirmation' => 'password#123',
            ]
        );

        $response->assertStatus(201)
            ->assertJsonStructure(
                [
                    'access_token',
                    'token_type',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                ]
            );
    }

    /**
     * Usuário pode fazer login
     *
     * @return void
     */
    public function test_users_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'user@gestaoclick.com.br',
            'password' => bcrypt('password#123'),
        ]);

        $response = $this->postJson(
            '/api/auth/login',
            [
                'email' => 'user@gestaoclick.com.br',
                'password' => 'password#123',
            ]
        );

        $response->assertStatus(200)
            ->assertJsonStructure(
                [
                    'access_token',
                    'token_type',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                ]
            );
    }

    /**
     * Usuário não pode realizar login com as credenciais inválidas
     *
     * @return void
     */
    public function  test_users_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create(
            [
                'email' => 'user@gestaoclick.com.br',
                'password' => bcrypt('password#123'),
            ]
        );

        $response = $this->postJson(
            '/api/auth/login',
            [
                'email' => 'user@gestaoclick.com.br',
                'password' => 'senha-incorreta',
            ]
        );

        $response->assertStatus(401)
            ->assertJson(
                [
                    'message' => 'Credenciais inválidas',
                ]
            );
    }

    /**
     * Usuário pode ver seu perfil
     *
     * @return void
     */
    public function test_users_can_get_their_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('Test Token')->plainTextToken;

        $response = $this->withHeaders(
          [
              'Authorization' => 'Bearer ' . $token,
          ]
        )
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson(
                [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            );
    }

    /**
     * Usuário pode fazer logout
     *
     * @return void
     */
    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('Test Token')->plainTextToken;

        $response = $this->withHeaders(
            [
                'Authorization' => 'Bearer ' . $token,
            ]
        )->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(
                [
                    'message' => 'Logout realizado com sucesso',
                ]
            );
    }
}
