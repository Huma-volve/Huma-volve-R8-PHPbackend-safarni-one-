<?php

namespace Tests\Feature\Api;

use App\Enums\OtpType;
use App\Enums\UserRole;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('public');
    }

    /*
    |--------------------------------------------------------------------------
    | Registration Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Test User',
                    'email' => 'test@gmail.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@gmail.com',
            'is_verified' => false,
        ]);

        // Verify OTP was created
        $this->assertDatabaseHas('otps', [
            'email' => 'test@gmail.com',
            'type' => OtpType::VERIFICATION->value,
        ]);
    }

    public function test_registration_validates_password_policy(): void
    {
        // Password too short
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password' => 'Aa1!',
            'password_confirmation' => 'Aa1!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // Password missing uppercase
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test2@gmail.com',
            'password' => 'password1!',
            'password_confirmation' => 'password1!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // Password missing special character
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test3@gmail.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // Password too long (>255)
        $longPassword = str_repeat('A', 256).'1!';
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test4@gmail.com',
            'password' => $longPassword,
            'password_confirmation' => $longPassword,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_validates_name_strict_rules(): void
    {
        // Name starts with space (TrimStrings middleware cleans this)
        $response = $this->postJson('/api/auth/register', [
            'name' => ' Test User',
            'email' => 'test_name1@gmail.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);
        // Should succeed as it gets trimmed to "Test User"
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Test User',
                ],
            ]);

        // Name ends with space (TrimStrings middleware cleans this)
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User ',
            'email' => 'test_name2@gmail.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);
        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Test User',
                ],
            ]);

        // Double space between words (TrimStrings does NOT clean this)
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test  User',
            'email' => 'test_name3@gmail.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['name']);

        // Special characters (not allowed)
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test@User',
            'email' => 'test_name4@gmail.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['name']);

        // Valid name
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test-User_123',
            'email' => 'test_valid@gmail.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);
        $response->assertStatus(201);
    }

    public function test_registration_validates_email_strict_rules(): void
    {
        // Uppercase letters
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'Test@gmail.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['email']);

        // Non-English characters (not easy to test reliably if DB/PHP allows it, but regex should catch it)
        // Trying a valid unicode char in email which we want to forbid
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'tést@gmail.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_registration_validates_email_is_gmail(): void
    {
        // Non-Gmail email
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@yahoo.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['email']);

        // Gmail subdomain (should likely fail based on regex)
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@sub.gmail.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@gmail.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'existing@gmail.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /*
    |--------------------------------------------------------------------------
    | OTP Verification Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_can_verify_email_with_valid_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'verify@gmail.com',
            'is_verified' => false,
        ]);

        Otp::create([
            'email' => 'verify@gmail.com',
            'code' => '1234',
            'type' => OtpType::VERIFICATION->value,
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/auth/verify', [
            'email' => 'verify@gmail.com',
            'code' => '1234',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email verified successfully.',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'verify@gmail.com',
            'is_verified' => true,
        ]);
    }

    public function test_verification_fails_with_invalid_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'verify@gmail.com',
            'is_verified' => false,
        ]);

        Otp::create([
            'email' => 'verify@gmail.com',
            'code' => '1234',
            'type' => OtpType::VERIFICATION->value,
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/auth/verify', [
            'email' => 'verify@gmail.com',
            'code' => '9999',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_verification_fails_with_expired_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'verify@gmail.com',
            'is_verified' => false,
        ]);

        Otp::create([
            'email' => 'verify@gmail.com',
            'code' => '1234',
            'type' => OtpType::VERIFICATION->value,
            'expires_at' => now()->subMinutes(1), // Expired
        ]);

        $response = $this->postJson('/api/auth/verify', [
            'email' => 'verify@gmail.com',
            'code' => '1234',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_otp_is_single_use(): void
    {
        $user = User::factory()->create([
            'email' => 'verify@gmail.com',
            'is_verified' => false,
        ]);

        Otp::create([
            'email' => 'verify@gmail.com',
            'code' => '1234',
            'type' => OtpType::VERIFICATION->value,
            'expires_at' => now()->addMinutes(10),
        ]);

        // First verification should succeed
        $response = $this->postJson('/api/auth/verify', [
            'email' => 'verify@gmail.com',
            'code' => '1234',
        ]);

        $response->assertStatus(200);

        // Reset user verification for second attempt
        $user->update(['is_verified' => false]);

        // Second attempt with same OTP should fail
        $response = $this->postJson('/api/auth/verify', [
            'email' => 'verify@gmail.com',
            'code' => '1234',
        ]);

        $response->assertStatus(400);
    }

    /*
    |--------------------------------------------------------------------------
    | Login Tests
    |--------------------------------------------------------------------------
    */

    public function test_verified_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'login@gmail.com',
            'password' => Hash::make('Password1!'),
            'is_verified' => true,
            'role' => UserRole::USER->value,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'login@gmail.com',
            'password' => 'Password1!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                    ],
                ],
            ]);
    }

    public function test_unverified_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'unverified@gmail.com',
            'password' => Hash::make('Password1!'),
            'is_verified' => false,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'unverified@gmail.com',
            'password' => 'Password1!',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'requires_verification' => true,
            ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'user@gmail.com',
            'password' => Hash::make('Password1!'),
            'is_verified' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@gmail.com',
            'password' => 'WrongPassword!',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Password Reset Tests
    |--------------------------------------------------------------------------
    */

    public function test_forgot_password_sends_otp(): void
    {
        $user = User::factory()->create(['email' => 'forgot@gmail.com']);

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'forgot@gmail.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('otps', [
            'email' => 'forgot@gmail.com',
            'type' => OtpType::PASSWORD_RESET->value,
        ]);
    }

    public function test_reset_password_with_valid_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@gmail.com',
            'password' => Hash::make('OldPassword1!'),
        ]);

        Otp::create([
            'email' => 'reset@gmail.com',
            'code' => '5678',
            'type' => OtpType::PASSWORD_RESET->value,
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'reset@gmail.com',
            'code' => '5678',
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify can login with new password
        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword1!', $user->password));
    }

    /*
    |--------------------------------------------------------------------------
    | Google OAuth Tests
    |--------------------------------------------------------------------------
    */

    public function test_google_callback_exchanges_code_for_token(): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'fake_access_token',
                'expires_in' => 3600,
            ], 200),
            'www.googleapis.com/oauth2/v2/userinfo' => Http::response([
                'id' => '123456789',
                'email' => 'google@gmail.com',
                'name' => 'Google User',
                'picture' => 'https://example.com/avatar.jpg',
            ], 200),
        ]);

        $response = $this->getJson('/api/auth/google/callback?code=valid_code');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful.',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'google@gmail.com',
            'google_id' => '123456789',
        ]);
    }

    public function test_google_redirect_uses_dynamic_uri(): void
    {
        $customRedirect = 'https://myapp.com/callback';
        $response = $this->getJson("/api/auth/google?redirect_uri={$customRedirect}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $url = $response->json('url');
        $this->assertStringContainsString('redirect_uri=' . urlencode($customRedirect), $url);
    }

    public function test_google_callback_uses_dynamic_uri(): void
    {
        $customRedirect = 'https://myapp.com/callback';

        Http::fake([
            'oauth2.googleapis.com/token' => function ($request) use ($customRedirect) {
                return $request['redirect_uri'] === $customRedirect &&
                       $request['code'] === 'valid_code'
                    ? Http::response(['access_token' => 'fake_token'], 200)
                    : Http::response(['error' => 'invalid_request'], 400);
            },
            'www.googleapis.com/oauth2/v2/userinfo' => Http::response([
                'id' => '987654321',
                'email' => 'dynamic@gmail.com',
                'name' => 'Dynamic User',
            ], 200),
        ]);

        $response = $this->getJson("/api/auth/google/callback?code=valid_code&redirect_uri={$customRedirect}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'dynamic@gmail.com',
            'google_id' => '987654321',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Profile Tests
    |--------------------------------------------------------------------------
    */

    public function test_authenticated_user_can_view_profile(): void
    {
        $user = User::factory()->create([
            'is_verified' => true,
            'role' => UserRole::USER->value,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create([
            'is_verified' => true,
            'role' => UserRole::USER->value,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/profile', [
            'name' => 'Updated Name',
            'phone' => '01012345678', // Valid Egyptian
            'location' => 'Cairo, Egypt',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Updated Name',
                    'phone' => '01012345678',
                ],
            ]);
    }

    public function test_profile_update_validates_phone_egyptian_rules(): void
    {
        $user = User::factory()->create([
            'is_verified' => true,
            'role' => UserRole::USER->value,
        ]);

        Sanctum::actingAs($user);

        // Invalid: Too short
        $response = $this->putJson('/api/profile', [
            'phone' => '0101234',
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['phone']);

        // Invalid: Not starting with 01
        $response = $this->putJson('/api/profile', [
            'phone' => '02012345678',
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['phone']);

        // Invalid: Characters
        $response = $this->putJson('/api/profile', [
            'phone' => '010123abc78',
        ]);
        $response->assertStatus(422)->assertJsonValidationErrors(['phone']);

        // Valid: 010...
        $response = $this->putJson('/api/profile', [
            'phone' => '01012345678',
        ]);
        $response->assertStatus(200);

        // Valid: 011...
        $response = $this->putJson('/api/profile', [
            'phone' => '01112345678',
        ]);
        $response->assertStatus(200);

        // Valid: 012...
        $response = $this->putJson('/api/profile', [
            'phone' => '01212345678',
        ]);
        $response->assertStatus(200);

        // Valid: 015...
        $response = $this->putJson('/api/profile', [
            'phone' => '01512345678',
        ]);
        $response->assertStatus(200);

        // Valid: +201...
        $response = $this->putJson('/api/profile', [
            'phone' => '+201012345678',
        ]);
        $response->assertStatus(200);
    }

    public function test_email_change_triggers_reverification(): void
    {
        $user = User::factory()->create([
            'email' => 'old@gmail.com',
            'is_verified' => true,
            'role' => UserRole::USER->value,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/profile', [
            'email' => 'new@gmail.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'requires_verification' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new@gmail.com',
            'is_verified' => false,
        ]);

        // Verify OTP was created for new email
        $this->assertDatabaseHas('otps', [
            'email' => 'new@gmail.com',
            'type' => OtpType::VERIFICATION->value,
        ]);
    }

    public function test_user_can_upload_profile_image(): void
    {
        $user = User::factory()->create([
            'is_verified' => true,
            'role' => UserRole::USER->value,
        ]);

        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->putJson('/api/profile', [
            'profile_image' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $user->refresh();
        $this->assertNotNull($user->profile_image);
        Storage::disk('public')->assertExists($user->profile_image);
    }

    /*
    |--------------------------------------------------------------------------
    | Password Change Tests
    |--------------------------------------------------------------------------
    */

    public function test_verified_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPass1!'),
            'is_verified' => true,
            'role' => UserRole::USER->value,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/profile/password', [
            'current_password' => 'CurrentPass1!',
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword1!', $user->password));
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPass1!'),
            'is_verified' => true,
            'role' => UserRole::USER->value,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/profile/password', [
            'current_password' => 'WrongPassword1!',
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Account Lifecycle Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_can_deactivate_account(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'is_verified' => true,
            'status' => 'active',
            'role' => UserRole::USER->value,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/profile/deactivate', [
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'inactive',
            'is_verified' => false,
        ]);
    }

    public function test_user_can_delete_account(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'is_verified' => true,
            'role' => UserRole::USER->value,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/profile', [
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // User should be soft deleted
        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Logout Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'is_verified' => true,
            'role' => UserRole::USER->value,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Role-Based Access Tests
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->create([
            'is_verified' => true,
            'role' => UserRole::ADMIN->value,
        ]);

        Sanctum::actingAs($admin);

        // Try to access an admin route (create airport)
        // This will fail validation but should not return 403
        $response = $this->postJson('/api/admin/airports', []);

        $this->assertNotEquals(403, $response->status());
    }

    public function test_regular_user_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create([
            'is_verified' => true,
            'role' => UserRole::USER->value,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/admin/airports', [
            'name' => 'Test Airport',
            'code' => 'TST',
        ]);

        $response->assertStatus(403);
    }
}
