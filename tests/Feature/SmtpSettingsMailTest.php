<?php

namespace Tests\Feature;

use RuntimeException;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SmtpSettingsMailTest extends TestCase
{
    public function test_mail_test_explains_microsoft_authentication_failures(): void
    {
        $this->withoutMiddleware();
        Mail::shouldReceive('raw')
            ->once()
            ->andThrow(new RuntimeException('Microsoft Graph authentication failed with HTTP 401.'));

        $this->post(route('admin.settings.smtp.test'), ['email' => 'test@example.test'])
            ->assertRedirect()
            ->assertSessionHas('error', 'Microsoft Graph authentication failed. Check that the Tenant ID and Client ID match, and that the current Client Secret Value (not its ID) is configured.');
    }

    public function test_mail_test_explains_microsoft_send_permission_failures(): void
    {
        $this->withoutMiddleware();
        Mail::shouldReceive('raw')
            ->once()
            ->andThrow(new RuntimeException('Microsoft Graph sendMail failed with HTTP 403.'));

        $this->post(route('admin.settings.smtp.test'), ['email' => 'test@example.test'])
            ->assertRedirect()
            ->assertSessionHas('error', 'Microsoft Graph rejected the send request. Check Mail.Send admin consent and that the From Address is a permitted mailbox.');
    }
}
