<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_register_successfully()
    {
        $response = $this->postJson('/api/v1/auth/signup', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(201)->assertJsonStructure(['token', 'user']);
    }

    /** @test */
    public function a_user_cannot_register_with_existing_email()
    {
        User::factory()->create(['email' => 'exists@example.com']);

        $response = $this->postJson('/api/v1/auth/signup', [
            'name' => 'Test User',
            'email' => 'exists@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function a_user_can_login_and_get_a_token()
    {
        $user = User::factory()->create(['password' => bcrypt('secret')]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['token']);
    }

    /** @test */
    public function it_fails_login_with_invalid_credentials()
    {
        User::factory()->create(['email' => 'fail@test.com', 'password' => bcrypt('correct-pass')]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'fail@test.com',
            'password' => 'wrong-pass',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials']);
    }

    /** @test */
    public function it_can_logout_an_authenticated_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
        $this->assertEmpty($user->fresh()->tokens);
    }

    /** @test */
    public function it_fails_signup_if_passwords_do_not_match()
    {
        $response = $this->postJson('/api/v1/auth/signup', [
            'name' => 'John',
            'email' => 'john@test.com',
            'password' => 'secret123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertStatus(422);
    }
}
