<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function testUserRegistration()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201);

        // Check if response has data key
        if ($response->json('data')) {
            $response->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token'
                ]
            ]);
        } else {
            $response->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token'
            ]);
        }

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function testUserLogin()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200);

        // Check if response has data key
        if ($response->json('data')) {
            $response->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token'
                ]
            ]);
        } else {
            $response->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token'
            ]);
        }
    }

    public function testUserLogout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Logged out successfully'
                 ]);

        // Check that the token is deleted
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function testLoginWithInvalidCredentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct_password'),
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'wrong_password',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function testProtectedRouteWithoutAuthentication()
    {
        $response = $this->getJson('/api/languages');

        $response->assertStatus(401);
    }
}
