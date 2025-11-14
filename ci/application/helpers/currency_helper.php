<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Currency Helper
 * Provides multi-currency support functions
 */

/**
 * Get list of supported currencies
 * @return array
 */
function get_currencies() {
    return [
        'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹'],
        'USD' => ['name' => 'US Dollar', 'symbol' => '$'],
        'EUR' => ['name' => 'Euro', 'symbol' => '€'],
        'GBP' => ['name' => 'British Pound', 'symbol' => '£'],
        'AED' => ['name' => 'UAE Dirham', 'symbol' => 'د.إ'],
        'SAR' => ['name' => 'Saudi Riyal', 'symbol' => 'ر.س'],
        'OMR' => ['name' => 'Omani Rial', 'symbol' => 'ر.ع.'],
        'KWD' => ['name' => 'Kuwaiti Dinar', 'symbol' => 'د.ك'],
        'QAR' => ['name' => 'Qatari Riyal', 'symbol' => 'ر.ق'],
        'BHD' => ['name' => 'Bahraini Dinar', 'symbol' => '.د.ب'],
        'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$'],
        'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$'],
        'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$'],
        'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM'],
        'THB' => ['name' => 'Thai Baht', 'symbol' => '฿']
    ];
}

/**
 * Get currency symbol by code
 * @param string $code Currency code (e.g., 'USD')
 * @return string Currency symbol
 */
function get_currency_symbol($code) {
    $currencies = get_currencies();
    return isset($currencies[$code]) ? $currencies[$code]['symbol'] : $code;
}

/**
 * Get currency name by code
 * @param string $code Currency code (e.g., 'USD')
 * @return string Currency name
 */
function get_currency_name($code) {
    $currencies = get_currencies();
    return isset($currencies[$code]) ? $currencies[$code]['name'] : $code;
}

/**
 * Get exchange rate from API
 * Uses exchangerate-api.com (free tier)
 * @param string $from Source currency code
 * @param string $to Target currency code
 * @return float|false Exchange rate or false if API fails
 */
function get_exchange_rate($from = 'USD', $to = 'INR') {
    // If both currencies are the same, return 1
    if ($from == $to) {
        return 1.000000;
    }

    // Try to fetch from API
    $url = "https://api.exchangerate-api.com/v4/latest/{$from}";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response && $httpCode == 200) {
        $data = json_decode($response, true);
        if (isset($data['rates'][$to])) {
            return round($data['rates'][$to], 6);
        }
    }

    return false; // API failed, manual entry required
}

/**
 * Format amount with currency symbol
 * @param float $amount
 * @param string $currency Currency code
 * @param int $decimals Number of decimal places
 * @return string Formatted amount
 */
function format_currency($amount, $currency = 'INR', $decimals = 2) {
    $symbol = get_currency_symbol($currency);
    $formatted = number_format($amount, $decimals);

    // For currencies that go after amount (like INR)
    if (in_array($currency, ['INR'])) {
        return $symbol . ' ' . $formatted;
    }

    // For currencies that go before amount (like USD, EUR)
    return $symbol . ' ' . $formatted;
}

/**
 * Check if currency is tax-free (foreign currency)
 * @param string $currency Currency code
 * @return bool True if tax-free
 */
function is_tax_free_currency($currency) {
    // Only INR has taxes in Indian system
    return ($currency != 'INR' && $currency != null && $currency != '');
}

/**
 * Convert amount between currencies
 * @param float $amount
 * @param string $from Source currency
 * @param string $to Target currency
 * @param float $rate Optional manual exchange rate
 * @return float Converted amount
 */
function convert_currency($amount, $from, $to, $rate = null) {
    if ($from == $to) {
        return $amount;
    }

    if ($rate === null) {
        $rate = get_exchange_rate($from, $to);
    }

    if ($rate === false) {
        return false;
    }

    return round($amount * $rate, 2);
}

?>
