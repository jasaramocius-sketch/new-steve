<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserEmailVerificationPolicyTest extends TestCase
{
    public function test_existing_unverified_users_are_not_required_to_verify_again(): void
    {
        $user = (new User())->forceFill([
            'email_verified_at' => null,
            'email_verification_required_at' => null,
        ]);

        $this->assertFalse($user->requiresEmailVerification());
    }

    public function test_new_unverified_users_must_verify_but_verified_users_do_not(): void
    {
        $user = (new User())->forceFill([
            'email_verified_at' => null,
            'email_verification_required_at' => now(),
        ]);

        $this->assertTrue($user->requiresEmailVerification());

        $user->email_verified_at = now();

        $this->assertFalse($user->requiresEmailVerification());
    }

    public function test_user_can_receive_the_framework_verification_notification(): void
    {
        Notification::fake();
        $user = (new User())->forceFill([
            'id' => 1,
            'email' => 'new-user@example.test',
        ]);

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
