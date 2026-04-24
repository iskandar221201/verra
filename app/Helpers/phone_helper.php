<?php

/**
 * Phone number normalization helper
 */

if (!function_exists('normalizePhoneNumber')) {
    /**
     * Normalize a phone number to 628xxx format.
     * - Strips all non-digit characters
     * - Converts leading 0 to 62
     *
     * @param string $phone Raw phone number
     * @return string Normalized phone number
     */
    function normalizePhoneNumber(string $phone): string
    {
        // Strip everything except digits
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert leading 0 to 62
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }
}
