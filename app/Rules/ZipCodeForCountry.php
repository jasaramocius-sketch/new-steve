<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ZipCodeForCountry implements ValidationRule
{
    public function __construct(private readonly ?string $country)
    {
    }

    public static function isUs(string $country): bool
    {
        $c = strtolower(trim($country));

        return in_array($c, ['united states', 'united states of america', 'usa', 'us'], true);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = trim((string) $value);
        if ($value === '') {
            return;
        }

        $country = trim((string) ($this->country ?? ''));
        $label = str_contains($attribute, 'postal') ? 'postal code' : 'ZIP code';

        if ($country !== '' && self::isUs($country)) {
            if (! preg_match('/^\d{5}(-\d{4})?$/', $value)) {
                $fail('Enter a valid US ZIP code (e.g. 12345 or 12345-6789).');
            }

            return;
        }

        if (! preg_match('/^[A-Za-z0-9\-\s]{3,20}$/', $value)) {
            $fail("Enter a valid {$label}.");
        }
    }
}
