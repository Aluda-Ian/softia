<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Starter company pages, policies and a first news post. Uses firstOrCreate so
 * re-seeding never overwrites text Softia has edited in the admin.
 * Policy wording is a draft for Softia (and its legal advisor) to review.
 */
class ContentSeeder extends Seeder
{
    private const DRAFT_NOTE = '<p><em>Draft for review by Softia before launch.</em></p>';

    public function run(): void
    {
        $contact = config('shop.contact');

        $pages = [
            ['company', 'About Us', 'about-us', 'Softia makes everyday hygiene essentials for Kenyan homes and institutions.',
                '<h2>Stay confident</h2><p>Softia Consumer Products Limited supplies premium tissue, professional cleaning chemicals, washroom hardware and food packaging to homes, hotels, hospitals, schools and offices across Kenya.</p>'
                .'<h2>What we stand for</h2><ul><li><strong>Quality you can feel</strong>: soft, strong products made to a consistent standard.</li><li><strong>Reliable supply</strong>: dependable stock and delivery from our Nairobi logistics hub.</li><li><strong>Fair pricing</strong>: transparent retail prices and wholesale rates for institutions.</li></ul>'],
            ['company', 'Contact Us', 'contact-us', 'Talk to the Softia team about orders, quotations and distribution.',
                "<h2>Get in touch</h2><ul><li><strong>Phone:</strong> {$contact['phone']}</li><li><strong>Email:</strong> {$contact['email']}</li><li><strong>Showroom:</strong> {$contact['showroom']}</li><li><strong>Logistics hub:</strong> {$contact['logistics_hub']}</li></ul><p>Our team responds to quotation requests within one business day.</p>"],
            ['company', 'Become a Distributor', 'become-a-distributor', 'Partner with Softia to supply hygiene essentials in your region.',
                "<h2>Grow with Softia</h2><p>We work with distributors and wholesalers across Kenya. Partners benefit from competitive trade pricing, reliable supply and marketing support.</p><p>To apply, email <strong>{$contact['email']}</strong> with your business name, location and the product lines you are interested in.</p>"],
            ['policies', 'Privacy Policy', 'privacy-policy', 'How Softia collects, uses and protects your personal data.',
                self::DRAFT_NOTE.'<h2>Information we collect</h2><p>When you place an order or request a quotation we collect your name, contact details, delivery address and, for businesses, company name and KRA PIN.</p><h2>How we use it</h2><p>We use this information only to process orders, deliver products, send quotations and invoices, and respond to enquiries, in line with the Kenya Data Protection Act, 2019.</p><h2>Your rights</h2><p>You may request access to, correction of or deletion of your personal data by contacting us.</p>'],
            ['policies', 'Terms & Conditions', 'terms-and-conditions', 'The terms that apply when you shop with Softia.',
                self::DRAFT_NOTE.'<h2>Orders</h2><p>All orders are subject to availability and confirmation by Softia. Quotations are valid for the period stated on the quotation.</p><h2>Pricing</h2><p>Prices are in Kenya Shillings. Wholesale prices apply from the minimum quantities shown on each product.</p><h2>Payment</h2><p>Retail orders are paid on delivery or by M-Pesa. Institutional orders are invoiced after quotation.</p>'],
            ['policies', 'Delivery Policy', 'delivery-policy', 'Where and how Softia delivers your order.',
                self::DRAFT_NOTE.'<h2>Delivery areas</h2><p>We deliver within Nairobi and surrounding areas from our logistics hub. Deliveries elsewhere in Kenya are arranged on request.</p><h2>Timelines</h2><p>Our team will call to confirm a delivery date once your order is confirmed. Delivery fees, where applicable, are confirmed before dispatch.</p>'],
            ['policies', 'Returns & Refunds', 'returns-and-refunds', 'What to do if something is wrong with your order.',
                self::DRAFT_NOTE.'<h2>Damaged or incorrect items</h2><p>Please inspect your order on delivery and report any damaged, missing or incorrect items within 48 hours.</p><h2>Refunds</h2><p>Approved refunds are made through the original payment method. Hygiene products cannot be returned once opened.</p>'],
        ];

        foreach ($pages as $order => [$group, $title, $slug, $excerpt, $body]) {
            Page::firstOrCreate(['slug' => $slug], [
                'title' => $title,
                'excerpt' => $excerpt,
                'body' => $body,
                'footer_group' => $group,
                'sort_order' => $order,
                'is_published' => true,
            ]);
        }

        Post::firstOrCreate(['slug' => 'softia-launches-online-shop'], [
            'title' => 'Softia launches its online shop',
            'excerpt' => 'Homes and institutions can now order Softia tissue, cleaning care, hardware and foil online, or request a business quotation.',
            'body' => '<p>We are excited to launch the Softia online shop. Individuals can order with pay on delivery or M-Pesa, and hotels, hospitals, schools and offices can build a cart and submit it as a quotation request.</p><p>Wholesale prices apply automatically once you reach the minimum quantity for a product.</p>',
            'cover_image' => null,
            'published_at' => now(),
        ]);
    }
}
