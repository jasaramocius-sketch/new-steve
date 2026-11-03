<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 3)->unique();
            $table->string('symbol', 12);
            $table->decimal('rate', 18, 8)->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $currencies = [
            ['USD', 'US Dollar', '$', 1],
            ['AUD', 'Australian Dollar', 'A$', 1.52],
            ['BRL', 'Brazilian Real', 'R$', 5.00],
            ['CAD', 'Canadian Dollar', 'C$', 1.37],
            ['CZK', 'Czech Koruna', 'Kč', 23.00],
            ['DKK', 'Danish Krone', 'kr', 6.90],
            ['EUR', 'Euro', '€', 0.92],
            ['HKD', 'Hong Kong Dollar', 'HK$', 7.80],
            ['HUF', 'Hungarian Forint', 'Ft', 360.00],
            ['ILS', 'Israeli New Shekel', '₪', 3.70],
            ['JPY', 'Japanese Yen', '¥', 157.00],
            ['MYR', 'Malaysian Ringgit', 'RM', 4.70],
            ['MXN', 'Mexican Peso', 'MX$', 17.00],
            ['NOK', 'Norwegian Krone', 'kr', 10.50],
            ['NZD', 'New Zealand Dollar', 'NZ$', 1.65],
            ['PHP', 'Philippine Peso', '₱', 58.00],
            ['PLN', 'Polish Zloty', 'zł', 4.00],
            ['GBP', 'British Pound', '£', 0.79],
            ['RUB', 'Russian Ruble', '₽', 90.00],
            ['SGD', 'Singapore Dollar', 'S$', 1.35],
            ['SEK', 'Swedish Krona', 'kr', 10.50],
            ['CHF', 'Swiss Franc', 'CHF', 0.89],
            ['THB', 'Thai Baht', '฿', 36.00],
            ['BDT', 'Bangladeshi Taka', '৳', 110.00],
            ['INR', 'Indian Rupee', '₹', 83.50],
        ];

        $now = now();
        DB::table('currencies')->insert(array_map(fn ($currency) => [
            'code' => $currency[0],
            'name' => $currency[1],
            'symbol' => $currency[2],
            'rate' => $currency[3],
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], $currencies));
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
