<?php

return [

    /*
    |--------------------------------------------------------------------------
    | VAT / Tax Settings
    |--------------------------------------------------------------------------
    |
    | Set vat_enabled to false to disable VAT entirely.
    | When enabled, vat_rate is expressed as a decimal (e.g. 0.075 = 7.5%).
    | vat_label is the string shown on invoices and checkout pages.
    |
    */

    'vat_enabled' => env('VAT_ENABLED', true),

    'vat_rate' => env('VAT_RATE', 0.075),

    'vat_label' => env('VAT_LABEL', 'VAT (7.5%)'),

];
