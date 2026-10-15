<?php

return [

    'currency' => 'KES',

    /*
    |--------------------------------------------------------------------------
    | Contact Details
    |--------------------------------------------------------------------------
    */

    'contact' => [
        'phone' => env('SHOP_PHONE', '+254 746 393 838'),
        'email' => env('SHOP_EMAIL', 'procurement@softia.co.ke'),
        'showroom' => env('SHOP_SHOWROOM', 'Eastern Bypass, Nairobi'),
        'logistics_hub' => env('SHOP_LOGISTICS_HUB', 'Nairobi Area'),
        'whatsapp' => env('SHOP_WHATSAPP'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retail Payment Methods
    |--------------------------------------------------------------------------
    |
    | Toggled in Admin > System > Settings. Business orders always use invoicing.
    |
    */

    'payments' => [
        'pay_on_delivery' => (bool) env('PAY_ON_DELIVERY', true),
        'mpesa_manual' => (bool) env('MPESA_MANUAL', true),
    ],

    'mail_via_smtp' => false,

    'analytics' => [
        'ga4_id' => env('GA4_ID'),
        'meta_pixel_id' => env('META_PIXEL_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | M-Pesa (manual)
    |--------------------------------------------------------------------------
    |
    | Customers pay to this Paybill/Till and enter the M-Pesa code at checkout.
    | Staff verify the code in the admin panel. Replace with Daraja later.
    |
    */

    'mpesa' => [
        'paybill' => env('MPESA_PAYBILL'),
        'account' => env('MPESA_ACCOUNT', 'Order reference'),
        'till' => env('MPESA_TILL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pages Managed in the SEO Manager
    |--------------------------------------------------------------------------
    |
    | Route name => label. Product and product line pages are managed per record.
    |
    */

    'seo_pages' => [
        'home' => 'Home page',
        'cart' => 'Cart',
        'news.index' => 'News',
        'contact' => 'Contact',
    ],

    'default_seo' => [
        'title' => 'Softia | Hygiene & Packaging Essentials in Kenya',
        'description' => 'Premium tissue, cleaning chemicals, washroom hardware and food packaging for homes and institutions across Kenya. Buy online or request a business quotation.',
        'image' => 'images/rolls-6-pack.jpg',
    ],

];
