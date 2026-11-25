<?php

namespace Tests\Feature;

use RuntimeException;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
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

    public function test_mail_test_explains_smtp_authentication_failures(): void
    {
        $this->withoutMiddleware();
        Mail::shouldReceive('raw')
            ->once()
            ->andThrow(new TransportException('535 5.7.8 Username and Password not accepted.'));

        $this->post(route('admin.settings.smtp.test'), ['email' => 'test@example.test'])
            ->assertRedirect()
            ->assertSessionHas('error', 'SMTP authentication was rejected. For Gmail, turn on 2-Step Verification and use a 16-character App Password instead of your normal Google password.');
    }

    public function test_mail_test_explains_gmail_app_password_required_failures(): void
    {
        $this->withoutMiddleware();
        Mail::shouldReceive('raw')
            ->once()
            ->andThrow(new TransportException('534 5.7.9 Application-specific password required.'));

        $this->post(route('admin.settings.smtp.test'), ['email' => 'test@example.test'])
            ->assertRedirect()
            ->assertSessionHas('error', 'SMTP authentication was rejected. For Gmail, turn on 2-Step Verification and use a 16-character App Password instead of your normal Google password.');
    }

    public function test_mail_test_explains_smtp_connection_failures(): void
    {
        $this->withoutMiddleware();
        Mail::shouldReceive('raw')
            ->once()
            ->andThrow(new TransportException('Connection to "smtp.example.test:587" timed out.'));

        $this->post(route('admin.settings.smtp.test'), ['email' => 'test@example.test'])
            ->assertRedirect()
            ->assertSessionHas('error', 'Could not connect to the SMTP server. Check the host, port and encryption settings.');
    }
}
