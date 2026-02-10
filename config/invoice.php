<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Invoice Company Information
    |--------------------------------------------------------------------------
    |
    | These values are used when generating invoices for subscriptions,
    | digital product purchases, and tips. Update these with your actual
    | company information.
    |
    */

    'company_name' => env('INVOICE_COMPANY_NAME', 'NextGen Being'),
    'company_address' => env('INVOICE_COMPANY_ADDRESS', '123 Main Street, City, State 12345, USA'),
    'company_phone' => env('INVOICE_COMPANY_PHONE', '+1 (555) 123-4567'),
    'company_email' => env('INVOICE_COMPANY_EMAIL', 'billing@nextgenbeing.com'),
    'company_website' => env('INVOICE_COMPANY_WEBSITE', env('APP_URL', 'https://nextgenbeing.com')),

    // Tax identification number (EIN for US companies, VAT for EU, etc.)
    'company_ein' => env('INVOICE_COMPANY_EIN', ''),
    'company_vat' => env('INVOICE_COMPANY_VAT', ''),

    /*
    |--------------------------------------------------------------------------
    | Invoice Settings
    |--------------------------------------------------------------------------
    */

    'logo_path' => env('INVOICE_LOGO_PATH', 'images/logo.png'),
    'currency' => env('INVOICE_CURRENCY', 'USD'),
    'currency_symbol' => env('INVOICE_CURRENCY_SYMBOL', '$'),

    // Invoice numbering
    'prefix' => env('INVOICE_PREFIX', 'INV'),
    'number_format' => env('INVOICE_NUMBER_FORMAT', '{prefix}-{year}-{number}'), // e.g., INV-2026-00001
    'starting_number' => env('INVOICE_STARTING_NUMBER', 1),

    /*
    |--------------------------------------------------------------------------
    | Tax Settings
    |--------------------------------------------------------------------------
    */

    'tax_enabled' => env('INVOICE_TAX_ENABLED', false),
    'tax_rate' => env('INVOICE_TAX_RATE', 0), // Percentage (e.g., 8.5 for 8.5%)
    'tax_name' => env('INVOICE_TAX_NAME', 'Sales Tax'),

    /*
    |--------------------------------------------------------------------------
    | Payment Terms
    |--------------------------------------------------------------------------
    */

    'payment_terms' => env('INVOICE_PAYMENT_TERMS', 'Payment is due immediately.'),
    'notes' => env('INVOICE_NOTES', 'Thank you for your business!'),

    /*
    |--------------------------------------------------------------------------
    | Invoice Footer
    |--------------------------------------------------------------------------
    */

    'footer' => env('INVOICE_FOOTER', 'This is a computer-generated invoice. No signature required.'),
];
