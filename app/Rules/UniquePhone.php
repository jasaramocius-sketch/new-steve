<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniquePhone implements ValidationRule
{
    public function __construct(
        private ?int $ignoreUserId = null,
        private ?string $currentPhone = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalized = $this->normalize((string) $value);

        if ($normalized === '') {
            if (trim((string) $value) !== '') {
                $fail('Please enter a valid phone number.');
            }

            return;
        }

        // Keep the current phone editable even if legacy duplicate numbers exist.
        if ($this->currentPhone !== null && $normalized === $this->normalize($this->currentPhone)) {
            return;
        }

        $query = User::withTrashed()->whereNotNull('phone');

        if ($this->ignoreUserId !== null) {
            $query->where('id', '!=', $this->ignoreUserId);
        }

        $exists = $query->pluck('phone')->contains(
            fn ($phone) => $this->normalize((string) $phone) === $normalized
        );

        if ($exists) {
            $fail('This phone number is already registered with another account.');
        }
    }

    private function normalize(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }
}
