<?php
declare(strict_types=1);

namespace Core;

final class Validator
{
    public static function email(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function required(string $value): bool
    {
        return trim($value) !== '';
    }

    public static function minLength(string $value, int $min): bool
    {
        return mb_strlen($value) >= $min;
    }

    public static function cpfCnpj(string $value): bool
    {
        $digits = preg_replace('/\D+/', '', $value);
        if (!is_string($digits)) {
            return false;
        }

        if (strlen($digits) === 11) {
            return self::cpf($digits);
        }

        if (strlen($digits) === 14) {
            return self::cnpj($digits);
        }

        return false;
    }

    private static function cpf(string $digits): bool
    {
        if (preg_match('/^(\d)\1{10}$/', $digits)) {
            return false;
        }

        $sum = 0;
        for ($i = 0, $w = 10; $i < 9; $i++, $w--) {
            $sum += ((int)$digits[$i]) * $w;
        }
        $mod = $sum % 11;
        $d1 = ($mod < 2) ? 0 : 11 - $mod;

        $sum = 0;
        for ($i = 0, $w = 11; $i < 10; $i++, $w--) {
            $sum += ((int)$digits[$i]) * $w;
        }
        $mod = $sum % 11;
        $d2 = ($mod < 2) ? 0 : 11 - $mod;

        return $digits[9] === (string)$d1 && $digits[10] === (string)$d2;
    }

    private static function cnpj(string $digits): bool
    {
        if (preg_match('/^(\d)\1{13}$/', $digits)) {
            return false;
        }

        $weights1 = [5,4,3,2,9,8,7,6,5,4,3,2];
        $weights2 = [6,5,4,3,2,9,8,7,6,5,4,3,2];

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += ((int)$digits[$i]) * $weights1[$i];
        }
        $mod = $sum % 11;
        $d1 = ($mod < 2) ? 0 : 11 - $mod;

        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += ((int)$digits[$i]) * $weights2[$i];
        }
        $mod = $sum % 11;
        $d2 = ($mod < 2) ? 0 : 11 - $mod;

        return $digits[12] === (string)$d1 && $digits[13] === (string)$d2;
    }
}
