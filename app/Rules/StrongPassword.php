<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || mb_strlen($value) < 12 || mb_strlen($value) > 32) {
            $fail('Kata sandi harus terdiri dari 12 sampai 32 karakter.');
            return;
        }

        if (preg_match('/\A\s|\s\z/u', $value)) {
            $fail('Kata sandi tidak boleh diawali atau diakhiri spasi.');
            return;
        }

        $categories = [
            preg_match('/[A-Z]/', $value) === 1,
            preg_match('/[a-z]/', $value) === 1,
            preg_match('/[0-9]/', $value) === 1,
            preg_match('/[^A-Za-z0-9 ]/', $value) === 1,
        ];

        if (count(array_filter($categories)) < 3) {
            $fail('Kata sandi harus memuat karakter dari minimal 3 kategori: huruf besar, huruf kecil, angka, dan simbol.');
        }
    }
}
