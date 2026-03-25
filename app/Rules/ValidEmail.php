<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final class ValidEmail implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            $fail('The :attribute must be a valid email address.');

            return;
        }

        // Must be all ASCII (no unicode)
        if (! mb_check_encoding($value, 'ASCII') || preg_match('/[^\x20-\x7E]/', $value)) {
            $fail('The :attribute must be a valid email address.');

            return;
        }

        // Must be all lowercase
        if ($value !== mb_strtolower($value)) {
            $fail('The :attribute must be a valid email address.');

            return;
        }

        // No quoted strings
        if (str_contains($value, '"') || str_contains($value, "'")) {
            $fail('The :attribute must be a valid email address.');

            return;
        }

        // Split into local and domain
        $parts = explode('@', $value);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            $fail('The :attribute must be a valid email address.');

            return;
        }

        [$local, $domain] = $parts;

        // No consecutive dots
        if (str_contains($local, '..') || str_contains($domain, '..')) {
            $fail('The :attribute must be a valid email address.');

            return;
        }

        // Domain must contain at least one dot and not start with one
        if (! str_contains($domain, '.') || str_starts_with($domain, '.') || str_ends_with($domain, '.')) {
            $fail('The :attribute must be a valid email address.');

            return;
        }

        // No IP addresses (bare or bracketed)
        if (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $domain) || str_starts_with($domain, '[')) {
            $fail('The :attribute must be a valid email address.');

            return;
        }

        // Domain labels must not start or end with a hyphen, and must be valid
        $labels = explode('.', $domain);
        foreach ($labels as $label) {
            if ($label === '' || str_starts_with($label, '-') || str_ends_with($label, '-')) {
                $fail('The :attribute must be a valid email address.');

                return;
            }

            // Only allow alphanumeric and hyphens in domain labels
            if (! preg_match('/^[a-z0-9\-]+$/', $label)) {
                $fail('The :attribute must be a valid email address.');

                return;
            }
        }

        // TLD must be at least 2 characters (no single-label domains)
        $tld = end($labels);
        if (mb_strlen($tld) < 2) {
            $fail('The :attribute must be a valid email address.');

            return;
        }

        // Domain must have at least 2 labels (domain + TLD)
        if (count($labels) < 2) {
            $fail('The :attribute must be a valid email address.');

            return;
        }

        // Validate local part: only allow a-z, 0-9, dot, hyphen, plus, underscore
        if (! preg_match('/^[a-z0-9.+_\-]+$/', $local)) {
            $fail('The :attribute must be a valid email address.');
        }
    }
}
