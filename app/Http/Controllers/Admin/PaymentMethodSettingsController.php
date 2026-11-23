<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\PaymentMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PaymentMethodSettingsController extends Controller
{
    public function index()
    {
        $definitions = PaymentMethods::definitions();
        $settings = [];

        foreach ($definitions as $provider => $definition) {
            $stored = PaymentMethods::credentials($provider);
            $fields = [];
            foreach ($definition['fields'] as $field) {
                $value = $stored[$field['name']] ?? ($field['default'] ?? '');
                $isSecret = (bool) ($field['secret'] ?? false);
                $field['value'] = $value;
                $field['configured'] = $isSecret && $value !== '';
                $field['masked'] = $field['configured'] ? $this->maskSecret((string) $value) : '';
                $fields[] = $field;
            }

            $settings[] = [
                'provider' => $provider,
                'title' => $definition['title'],
                'fields' => $fields,
                'sandbox' => $definition['sandbox'] ?? null,
                'sandboxEnabled' => isset($definition['sandbox']) && (bool) ($stored[$definition['sandbox']] ?? $definition['sandboxDefault'] ?? false),
            ];
        }

        return view('admin.settings.payment-methods', compact('settings'));
    }

    public function update(Request $request, string $provider)
    {
        $definitions = PaymentMethods::definitions();
        abort_unless(array_key_exists($provider, $definitions), 404);
        $definition = $definitions[$provider];
        $existing = PaymentMethods::credentials($provider);

        $rules = [];
        foreach ($definition['fields'] as $field) {
            $required = $field['required'] ?? false;
            if ($field['secret'] && ! empty($existing[$field['name']])) {
                $required = false;
            }
            $rules[$field['name']] = [
                $required ? 'required' : 'nullable',
                'string',
                'max:4096',
            ];
            if (($field['type'] ?? 'text') === 'email') {
                $rules[$field['name']][] = 'email';
            }
        }
        if (isset($definition['sandbox'])) {
            $rules[$definition['sandbox']] = 'nullable|boolean';
        }

        $validated = $request->validate($rules);
        $credentials = [];
        foreach ($definition['fields'] as $field) {
            $name = $field['name'];
            $value = $validated[$name] ?? '';
            if ($field['secret'] && $value === '') {
                $value = $existing[$name] ?? '';
            }
            $credentials[$name] = $value;
        }
        if (isset($definition['sandbox'])) {
            $credentials[$definition['sandbox']] = $request->boolean($definition['sandbox']);
        }

        Setting::set('payment_method_'.$provider, Crypt::encryptString(json_encode($credentials, JSON_THROW_ON_ERROR)));

        return redirect()->route('admin.settings.payment-methods')->with('success', $definition['title'].' settings saved.');
    }

    /**
     * Kept for admin metadata; the view renders the saved secret as a
     * password-masked readonly field with an explicit reveal control.
     */
    private function maskSecret(string $value): string
    {
        $length = strlen($value);

        if ($length === 0) {
            return '';
        }

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', 8).substr($value, -4);
    }
}
