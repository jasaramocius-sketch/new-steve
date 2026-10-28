<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiIntegrationController extends Controller
{
    protected array $providers = [
        'courier' => [
            'shiprocket' => ['name' => 'Shiprocket', 'auth_type' => 'bearer'],
            'delhivery' => ['name' => 'Delhivery', 'auth_type' => 'api_key'],
            'pickrr' => ['name' => 'Pickrr', 'auth_type' => 'bearer'],
            'nimbuspost' => ['name' => 'NimbusPost', 'auth_type' => 'bearer'],
            'clickpost' => ['name' => 'ClickPost', 'auth_type' => 'api_key'],
            'ecomexpress' => ['name' => 'Ecom Express', 'auth_type' => 'api_key'],
            'custom' => ['name' => 'Custom API', 'auth_type' => 'bearer'],
        ],
        'payment' => [
            'razorpay' => ['name' => 'Razorpay', 'auth_type' => 'basic'],
            'stripe' => ['name' => 'Stripe', 'auth_type' => 'api_key'],
            'payu' => ['name' => 'PayU', 'auth_type' => 'api_key'],
            'cashfree' => ['name' => 'Cashfree', 'auth_type' => 'api_key'],
            'ccavenue' => ['name' => 'CCAvenue', 'auth_type' => 'api_key'],
            'custom' => ['name' => 'Custom API', 'auth_type' => 'bearer'],
        ],
        'sms' => [
            'msg91' => ['name' => 'MSG91', 'auth_type' => 'api_key'],
            'textlocal' => ['name' => 'TextLocal', 'auth_type' => 'api_key'],
            'twilio' => ['name' => 'Twilio', 'auth_type' => 'basic'],
            'fast2sms' => ['name' => 'Fast2SMS', 'auth_type' => 'api_key'],
            'custom' => ['name' => 'Custom API', 'auth_type' => 'api_key'],
        ],
        'whatsapp' => [
            'meta' => ['name' => 'Meta WhatsApp Cloud', 'auth_type' => 'bearer'],
            'interakt' => ['name' => 'Interakt', 'auth_type' => 'api_key'],
            'wati' => ['name' => 'WATI', 'auth_type' => 'bearer'],
            'gupshup' => ['name' => 'Gupshup', 'auth_type' => 'api_key'],
            'custom' => ['name' => 'Custom API', 'auth_type' => 'bearer'],
        ],
        'email' => [
            'smtp' => ['name' => 'SMTP', 'auth_type' => 'basic'],
            'mailgun' => ['name' => 'Mailgun', 'auth_type' => 'api_key'],
            'sendgrid' => ['name' => 'SendGrid', 'auth_type' => 'api_key'],
            'brevo' => ['name' => 'Brevo', 'auth_type' => 'api_key'],
            'custom' => ['name' => 'Custom API', 'auth_type' => 'api_key'],
        ],
        'other' => [
            'custom' => ['name' => 'Custom API', 'auth_type' => 'bearer'],
        ],
    ];

    public function index()
    {
        $integrations = ApiIntegration::orderBy('type')->orderBy('priority', 'desc')->orderBy('id', 'desc')->get();
        return view('admin.api-integrations.index', compact('integrations'));
    }

    public function create()
    {
        $providers = $this->providers;
        return view('admin.api-integrations.create', compact('providers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:courier,payment,sms,email,whatsapp,other',
            'provider' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'base_url' => 'nullable|url|max:255',
            'auth_type' => 'required|in:none,bearer,api_key,basic,oauth',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'priority' => 'nullable|integer',
        ]);

        if ($request->boolean('is_default')) {
            ApiIntegration::where('type', $request->type)->update(['is_default' => false]);
        }

        $credentials = $request->input('credentials', []);
        if (!is_array($credentials)) {
            $credentials = [];
        }

        $endpoints = $request->input('endpoints', []);
        if (!is_array($endpoints)) {
            $endpoints = [];
        }

        $config = $request->input('config', []);
        if (!is_array($config)) {
            $config = [];
        }

        ApiIntegration::create([
            'type' => $request->type,
            'provider' => $request->provider,
            'name' => $request->name,
            'base_url' => $request->input('base_url'),
            'auth_type' => $request->auth_type,
            'credentials' => $credentials,
            'endpoints' => $endpoints,
            'config' => $config,
            'description' => $request->input('description'),
            'is_active' => $request->boolean('is_active'),
            'is_default' => $request->boolean('is_default'),
            'priority' => $request->integer('priority', 0),
        ]);

        return redirect()->route('admin.api-integrations.index')->with('success', 'API Integration added successfully.');
    }

    public function edit(ApiIntegration $apiIntegration)
    {
        $providers = $this->providers;
        return view('admin.api-integrations.edit', compact('apiIntegration', 'providers'));
    }

    public function update(Request $request, ApiIntegration $apiIntegration)
    {
        $request->validate([
            'type' => 'required|in:courier,payment,sms,email,whatsapp,other',
            'provider' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'base_url' => 'nullable|url|max:255',
            'auth_type' => 'required|in:none,bearer,api_key,basic,oauth',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'priority' => 'nullable|integer',
        ]);

        if ($request->boolean('is_default')) {
            ApiIntegration::where('type', $request->type)->where('id', '!=', $apiIntegration->id)->update(['is_default' => false]);
        }

        $credentials = $request->input('credentials', []);
        if (!is_array($credentials)) {
            $credentials = [];
        }

        $endpoints = $request->input('endpoints', []);
        if (!is_array($endpoints)) {
            $endpoints = [];
        }

        $config = $request->input('config', []);
        if (!is_array($config)) {
            $config = [];
        }

        $apiIntegration->update([
            'type' => $request->type,
            'provider' => $request->provider,
            'name' => $request->name,
            'base_url' => $request->input('base_url'),
            'auth_type' => $request->auth_type,
            'credentials' => $credentials,
            'endpoints' => $endpoints,
            'config' => $config,
            'description' => $request->input('description'),
            'is_active' => $request->boolean('is_active'),
            'is_default' => $request->boolean('is_default'),
            'priority' => $request->integer('priority', 0),
        ]);

        return redirect()->route('admin.api-integrations.index')->with('success', 'API Integration updated successfully.');
    }

    public function destroy(ApiIntegration $apiIntegration)
    {
        $apiIntegration->delete();
        return redirect()->route('admin.api-integrations.index')->with('success', 'API Integration deleted successfully.');
    }

    public function toggle(ApiIntegration $apiIntegration)
    {
        $apiIntegration->is_active = !$apiIntegration->is_active;
        $apiIntegration->save();
        return back()->with('success', 'Status updated successfully.');
    }

    public function setDefault(ApiIntegration $apiIntegration)
    {
        ApiIntegration::where('type', $apiIntegration->type)->update(['is_default' => false]);
        $apiIntegration->is_default = true;
        $apiIntegration->is_active = true;
        $apiIntegration->save();
        return back()->with('success', 'Default integration updated successfully.');
    }

    public function test(ApiIntegration $apiIntegration)
    {
        $creds = $apiIntegration->credentials;
        $baseUrl = $apiIntegration->base_url;

        try {
            if ($apiIntegration->provider === 'shiprocket') {
                if (empty($creds['email']) || empty($creds['password'])) {
                    $apiIntegration->last_tested_at = now();
                    $apiIntegration->last_test_result = 'Missing: email or password';
                    $apiIntegration->save();
                    return back()->with('error', 'Shiprocket requires email and password.');
                }

                $response = Http::timeout(30)->post('https://apiv2.shiprocket.in/v1/external/auth/login', [
                    'email' => $creds['email'],
                    'password' => $creds['password'],
                ]);

                if ($response->successful()) {
                    $apiIntegration->last_tested_at = now();
                    $apiIntegration->last_test_result = 'Success: ' . $response->status();
                    $apiIntegration->save();
                    return back()->with('success', 'Shiprocket connection successful.');
                }

                $apiIntegration->last_tested_at = now();
                $apiIntegration->last_test_result = 'Failed: ' . $response->status();
                $apiIntegration->save();
                return back()->with('error', 'Shiprocket connection failed with status ' . $response->status());
            }

            if ($apiIntegration->type === 'payment' && $apiIntegration->provider === 'razorpay') {
                if (empty($creds['key_id']) || empty($creds['key_secret'])) {
                    return back()->with('error', 'Razorpay requires key_id and key_secret.');
                }

                $response = Http::timeout(30)->withBasicAuth($creds['key_id'], $creds['key_secret'])->get('https://api.razorpay.com/v1/orders?count=1');
                if ($response->successful()) {
                    $apiIntegration->last_tested_at = now();
                    $apiIntegration->last_test_result = 'Success: ' . $response->status();
                    $apiIntegration->save();
                    return back()->with('success', 'Razorpay connection successful.');
                }

                $apiIntegration->last_tested_at = now();
                $apiIntegration->last_test_result = 'Failed: ' . $response->status();
                $apiIntegration->save();
                return back()->with('error', 'Razorpay connection failed.');
            }

            if ($baseUrl) {
                $response = Http::timeout(15)->get($baseUrl);
                $apiIntegration->last_tested_at = now();
                $apiIntegration->last_test_result = 'Ping: ' . $response->status();
                $apiIntegration->save();
                return back()->with('success', 'API pinged successfully with status ' . $response->status());
            }

            $apiIntegration->last_tested_at = now();
            $apiIntegration->last_test_result = 'No specific test available';
            $apiIntegration->save();
            return back()->with('info', 'No specific test available for this provider. Add base_url to test ping.');
        } catch (\Throwable $e) {
            $apiIntegration->last_tested_at = now();
            $apiIntegration->last_test_result = 'Error: ' . $e->getMessage();
            $apiIntegration->save();
            return back()->with('error', 'Connection failed: ' . $e->getMessage());
        }
    }

    public function getProviderFields(Request $request)
    {
        $type = $request->get('type');
        $provider = $request->get('provider');
        $fields = $this->getFieldsForProvider($type, $provider);
        return response()->json(['fields' => $fields]);
    }

    protected function getFieldsForProvider($type, $provider)
    {
        if ($type === 'courier' && $provider === 'shiprocket') {
            return [
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true],
                ['name' => 'pickup_location', 'label' => 'Pickup Location', 'type' => 'text', 'required' => false],
            ];
        }
        if ($type === 'courier' && $provider === 'delhivery') {
            return [
                ['name' => 'api_key', 'label' => 'API Key / Token', 'type' => 'text', 'required' => true],
                ['name' => 'client_id', 'label' => 'Client ID', 'type' => 'text', 'required' => false],
            ];
        }
        if ($type === 'courier' && $provider === 'pickrr') {
            return [
                ['name' => 'api_key', 'label' => 'API Key', 'type' => 'text', 'required' => true],
                ['name' => 'seller_id', 'label' => 'Seller ID', 'type' => 'text', 'required' => false],
            ];
        }
        if ($type === 'courier' && $provider === 'nimbuspost') {
            return [
                ['name' => 'api_key', 'label' => 'API Key', 'type' => 'text', 'required' => true],
                ['name' => 'api_secret', 'label' => 'API Secret', 'type' => 'password', 'required' => false],
            ];
        }
        if ($type === 'payment' && $provider === 'razorpay') {
            return [
                ['name' => 'key_id', 'label' => 'Key ID', 'type' => 'text', 'required' => true],
                ['name' => 'key_secret', 'label' => 'Key Secret', 'type' => 'password', 'required' => true],
            ];
        }
        if ($type === 'payment' && $provider === 'stripe') {
            return [
                ['name' => 'secret_key', 'label' => 'Secret Key', 'type' => 'password', 'required' => true],
                ['name' => 'publishable_key', 'label' => 'Publishable Key', 'type' => 'text', 'required' => false],
            ];
        }
        if ($type === 'sms' && $provider === 'msg91') {
            return [
                ['name' => 'auth_key', 'label' => 'Auth Key', 'type' => 'text', 'required' => true],
                ['name' => 'sender_id', 'label' => 'Sender ID', 'type' => 'text', 'required' => false],
                ['name' => 'route', 'label' => 'Route', 'type' => 'text', 'required' => false],
            ];
        }
        if ($type === 'whatsapp' && $provider === 'meta') {
            return [
                ['name' => 'access_token', 'label' => 'Access Token', 'type' => 'password', 'required' => true],
                ['name' => 'phone_number_id', 'label' => 'Phone Number ID', 'type' => 'text', 'required' => true],
                ['name' => 'business_account_id', 'label' => 'Business Account ID', 'type' => 'text', 'required' => false],
            ];
        }
        return [
            ['name' => 'api_key', 'label' => 'API Key / Token', 'type' => 'text', 'required' => false],
            ['name' => 'secret_key', 'label' => 'Secret Key', 'type' => 'password', 'required' => false],
            ['name' => 'username', 'label' => 'Username / Email', 'type' => 'text', 'required' => false],
            ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => false],
        ];
    }
}
