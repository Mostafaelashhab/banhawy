<?php

namespace App\Support;

/**
 * Egyptian phone normalization for click-to-chat / tel: links.
 *
 * Accepts any of these and returns the E.164 form (without +):
 *   01022345504     → 201022345504
 *   1022345504      → 201022345504   (missing leading 0)
 *   +201022345504   → 201022345504
 *   00201022345504  → 201022345504
 *   201022345504    → 201022345504
 *   "0102 234 5504" → 201022345504   (spaces/punctuation stripped)
 *
 * For wa.me you must NOT include the +, just digits.
 */
class Phone
{
    public const EG_CC = '20';

    /** Normalize for wa.me / Click-to-Chat (digits only, with country code). */
    public static function forWhatsapp(?string $raw): string
    {
        $digits = self::digits($raw);
        if ($digits === '') return '';

        // 0020XXXX...  → 20XXX...
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // Already has country code (12 digits starting with 20 + valid mobile prefix)
        if (str_starts_with($digits, self::EG_CC) && strlen($digits) >= 12) {
            return $digits;
        }

        // Local Egyptian: 11 digits starting with 0 (e.g. 01022345504)
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return self::EG_CC . substr($digits, 1);
        }

        // Missing leading zero: 10 digits starting with 1 (e.g. 1022345504)
        if (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            return self::EG_CC . $digits;
        }

        // Fallback: return whatever digits we have — best effort
        return $digits;
    }

    /** Normalize for `tel:` links — E.164 form WITH the + sign. */
    public static function forTel(?string $raw): string
    {
        $e164 = self::forWhatsapp($raw);
        return $e164 === '' ? '' : '+'.$e164;
    }

    /** Pretty Arabic-friendly format for display (keeps original if not Egyptian). */
    public static function forDisplay(?string $raw): string
    {
        $e164 = self::forWhatsapp($raw);
        if ($e164 === '' || !str_starts_with($e164, self::EG_CC) || strlen($e164) < 12) {
            return (string) $raw;
        }
        // 20 1 02 234 5504  → 010 2234 5504  (local-friendly)
        $local = '0' . substr($e164, 2);          // 01022345504
        return preg_replace('/^(\d{4})(\d{4})(\d{3})$/', '$1 $2 $3', $local) ?? $local;
    }

    private static function digits(?string $raw): string
    {
        return preg_replace('/\D/', '', (string) $raw) ?? '';
    }
}
