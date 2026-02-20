<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_verifies_password_correctly()
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret_password')
        ]);

        $this->assertTrue(Hash::check('secret_password', $user->password));
        $this->assertFalse(Hash::check('wrong_password', $user->password));
    }
}