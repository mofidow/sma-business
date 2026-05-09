<?php

declare(strict_types=1);

namespace Plugins\Payments\Gateways\Waafi;

/**
 * Maps raw WaafiPay error messages to bilingual Somali/English user messages.
 */
final class WaafiErrorHandler
{
    public static function humanize(string $raw): string
    {
        $lower = strtolower($raw);

        if (str_contains($lower, 'insufficient') || str_contains($lower, 'balance')) {
            return 'Lacagta WAAFI kuma filna. Ku dar lacag oo isku day. / Your WAAFI balance is too low. Top up and try again.';
        }

        if (str_contains($lower, 'declin') || str_contains($lower, 'rejected')) {
            return 'Lacag bixintu waa la diiday. Eeg WAAFI app-kaaga. / Payment was declined. Check your WAAFI app.';
        }

        if (str_contains($lower, 'timeout') || str_contains($lower, 'expired') || str_contains($lower, 'timed out')) {
            return 'Wakhtigii xaqiijinta wuu dhammaaday. Mar kale isku day. / Confirmation timed out. Please try again.';
        }

        if (str_contains($lower, 'invalid') && str_contains($lower, 'phone')) {
            return 'Taleefankaaga WAAFI sax maaha. / That WAAFI number is not valid.';
        }

        if (str_contains($lower, 'invalid account') || str_contains($lower, 'not found')) {
            return 'Akoonka WAAFI lama helin. Hubi lambarka. / WAAFI account not found. Check the number.';
        }

        if (str_contains($lower, 'duplicate')) {
            return 'Lacag bixin midkii hore ayaa jira. / Duplicate payment detected.';
        }

        return 'Lacag bixintu wax ma shaqayn. Isku day mar kale. / Payment did not go through. Please try again.';
    }
}
