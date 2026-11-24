<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class SmtpSettingsController extends Controller
{
    private const DRIVERS = ['smtp', 'sendmail', 'mailgun', 'microsoft', 'log'];

    public function index()
    {
        $settings = Setting::getAllAsArray();
        $driver = $settings['mail_driver'] ?? config('mail.default', 'smtp');
        if (in_array($driver, self::DRIVERS, true) === false) {
            $driver = 'smtp';
        }

        return view('backend.settings.smtp', [
            'driver' => $driver,
            'mailHost' => $settings['mail_host'] ?? config('mail.mailers.smtp.host', ''),
            'mailPort' => $settings['mail_port'] ?? config('mail.mailers.smtp.port', 587),
            'mailUsername' => $settings['mail_username'] ?? config('mail.mailers.smtp.username', ''),
            'mailPasswordConfigured' => ! empty($settings['mail_password']) || ! empty(config('mail.mailers.smtp.password')),
            'mailEncryption' => $settings['mail_encryption'] ?? 'tls',
            'mailFromAddress' => $settings['mail_from_address'] ?? config('mail.from.address', ''),
            'mailFromName' => $settings['mail_from_name'] ?? config('mail.from.name', config('app.name')),
            'mailgunDomain' => $settings['mailgun_domain'] ?? '',
            'mailgunSecretConfigured' => ! empty($settings['mailgun_secret']),
            'mailTenantId' => $settings['mail_tenant_id'] ?? config('services.microsoft_mail.tenant_id', ''),
            'mailClientId' => $settings['mail_client_id'] ?? config('services.microsoft_mail.client_id', ''),
            'mailClientSecretConfigured' => ! empty($settings['mail_client_secret'])
                || ! empty(config('services.microsoft_mail.client_secret')),
            'testEmail' => $settings['header_email'] ?? config('mail.from.address', ''),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'mail_driver' => ['required', Rule::in(self::DRIVERS)],
            'mail_host' => 'required_if:mail_driver,smtp|nullable|string|max:255',
            'mail_port' => 'required_if:mail_driver,smtp|nullable|integer|between:1,65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:4096',
            'mail_encryption' => 'nullable|in:ssl,tls,none',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
            'mailgun_domain' => 'required_if:mail_driver,mailgun|nullable|string|max:255',
            'mailgun_secret' => 'nullable|string|max:4096',
            'mail_tenant_id' => 'required_if:mail_driver,microsoft|nullable|uuid',
            'mail_client_id' => 'required_if:mail_driver,microsoft|nullable|uuid',
            'mail_client_secret' => 'nullable|string|max:4096',
        ]);

        if ($validated['mail_driver'] === 'mailgun' && ! $request->filled('mailgun_secret') && ! Setting::get('mailgun_secret')) {
            return back()->withErrors(['mailgun_secret' => 'Enter a Mailgun API key to use this driver.'])->withInput();
        }
        if (
            $validated['mail_driver'] === 'microsoft'
            && ! $request->filled('mail_client_secret')
            && ! Setting::get('mail_client_secret')
            && ! config('services.microsoft_mail.client_secret')
        ) {
            return back()->withErrors(['mail_client_secret' => 'Enter a Microsoft client secret to use this driver.'])->withInput();
        }

        foreach ([
            'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'mail_encryption',
            'mail_from_address', 'mail_from_name', 'mailgun_domain', 'mail_tenant_id', 'mail_client_id',
        ] as $key) {
            Setting::set($key, (string) ($validated[$key] ?? ''));
        }

        foreach (['mail_password', 'mailgun_secret', 'mail_client_secret'] as $key) {
            if ($request->filled($key)) {
                Setting::set($key, Crypt::encryptString($validated[$key]));
            }
        }

        return redirect()->route('admin.settings.smtp')->with('success', 'Mail configuration updated.');
    }

    public function test(Request $request)
    {
        $validated = $request->validate(['email' => 'required|email|max:255']);

        try {
            Mail::raw('This is a test message from '.config('app.name').'.', function ($message) use ($validated) {
                $message->to($validated['email'])->subject('Mail configuration test');
            });
        } catch (Throwable $exception) {
            $failureType = 'mail_transport';
            $httpStatus = null;
            $errorMessage = 'Test email could not be sent. Check the selected mail driver and configuration.';

            if (
                $exception instanceof RuntimeException
                && preg_match('/Microsoft Graph authentication failed with HTTP (\d{3})\./', $exception->getMessage(), $matches)
            ) {
                $failureType = 'microsoft_graph_authentication';
                $httpStatus = (int) $matches[1];
                $errorMessage = 'Microsoft Graph authentication failed. Check that the Tenant ID and Client ID match, and that the current Client Secret Value (not its ID) is configured.';
            } elseif (
                $exception instanceof RuntimeException
                && preg_match('/Microsoft Graph sendMail failed with HTTP (\d{3})\./', $exception->getMessage(), $matches)
            ) {
                $failureType = 'microsoft_graph_send';
                $httpStatus = (int) $matches[1];
                $errorMessage = 'Microsoft Graph rejected the send request. Check Mail.Send admin consent and that the From Address is a permitted mailbox.';
            }

            logger()->warning('Mail configuration test failed.', [
                'exception' => get_class($exception),
                'failure_type' => $failureType,
                'http_status' => $httpStatus,
            ]);

            return back()->with('error', $errorMessage);
        }

        $message = config('mail.default') === 'log'
            ? 'The message was written to the configured mail log.'
            : 'Test email sent successfully.';

        return back()->with('success', $message);
    }

    public static function decryptSetting(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return $value;
        }
    }
}
