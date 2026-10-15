<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeds the four Softia product lines with sections and starter products.
 * Prices are placeholders (KES) for Softia to replace in the admin panel.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->catalog() as $lineOrder => $line) {
            $productLine = Category::updateOrCreate(['slug' => $line['slug']], [
                'name' => $line['name'],
                'menu_label' => $line['menu_label'],
                'tagline' => $line['tagline'],
                'description' => $line['description'],
                'image' => null,
                'sort_order' => $lineOrder,
                'is_active' => true,
            ]);

            foreach ($line['sections'] as $sectionOrder => $section) {
                $sectionCategory = Category::updateOrCreate(['slug' => Str::slug($section['name'])], [
                    'parent_id' => $productLine->id,
                    'name' => $section['name'],
                    'sort_order' => $sectionOrder,
                    'is_active' => true,
                ]);

                foreach ($section['products'] as $index => [$name, $unit, $retail, $wholesale, $minQty, $image]) {
                    Product::updateOrCreate(['slug' => Str::slug($name)], [
                        'category_id' => $sectionCategory->id,
                        'name' => $name,
                        'sku' => sprintf('SFT-%s-%d%02d', $line['code'], $sectionOrder + 1, $index + 1),
                        'short_description' => $section['blurb'],
                        'description' => $section['blurb'].' Supplied by Softia for homes, offices and institutions across Kenya.',
                        'image' => $this->starterImage($image),
                        'unit' => $unit,
                        'retail_price' => $retail,
                        'wholesale_price' => $wholesale,
                        'min_wholesale_qty' => $minQty,
                        'stock' => 250,
                        'is_featured' => $index === 0,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }

    /**
     * Copies a bundled photo from public/images onto the public upload disk so
     * it behaves like an admin upload (editable and replaceable in Filament).
     */
    private function starterImage(?string $bundledPath): ?string
    {
        if ($bundledPath === null || ! file_exists(public_path($bundledPath))) {
            return null;
        }

        $diskPath = 'catalog/'.basename($bundledPath);

        if (! Storage::disk('public')->exists($diskPath)) {
            Storage::disk('public')->put($diskPath, file_get_contents(public_path($bundledPath)), 'public');
        }

        return $diskPath;
    }

    /**
     * Product row: [name, unit, retail price|null (quote only), wholesale price, min wholesale qty, image].
     *
     * @return list<array{slug: string, code: string, name: string, menu_label: string, tagline: string, description: string, image: ?string, sections: list<array{name: string, blurb: string, products: list<array{0: string, 1: string, 2: ?int, 3: ?int, 4: int, 5: ?string}>}>}>
     */
    private function catalog(): array
    {
        return [
            [
                'slug' => 'paper-products',
                'code' => 'PP',
                'name' => 'Paper Products',
                'menu_label' => 'Paper Products',
                'tagline' => 'Tissue, serviettes & towels',
                'description' => 'Soft, strong paper essentials for homes, hotels, hospitals and offices.',
                'image' => 'images/rolls-6-pack.jpg',
                'sections' => [
                    [
                        'name' => 'Toilet Tissue',
                        'blurb' => 'Premium 2-ply toilet tissue that is soft on skin and strong in use.',
                        'products' => [
                            ['Softia Premium Toilet Tissue 6-Pack', '6 rolls', 420, 380, 10, 'images/rolls-6-pack.jpg'],
                            ['Softia Premium Toilet Tissue 4-Pack', '4 rolls', 290, 260, 10, 'images/rolls-4-pack.jpg'],
                            ['Softia Premium Toilet Tissue Single Roll', '1 roll', 75, 65, 48, 'images/roll-single.jpg'],
                            ['Softia Jumbo Roll (Institutional)', 'Carton of 12', null, 3600, 5, 'images/roll-on-blue.jpg'],
                        ],
                    ],
                    [
                        'name' => 'Facial Tissue',
                        'blurb' => 'Soft facial tissue in a clean, pull-up box for desks and reception areas.',
                        'products' => [
                            ['Softia Facial Tissue Box', '100 sheets', 180, 160, 24, 'images/tissue-box.jpg'],
                        ],
                    ],
                    [
                        'name' => 'Serviettes',
                        'blurb' => 'Absorbent serviettes for restaurants, events and catering.',
                        'products' => [
                            ['Softia Serviettes 1-Ply', 'Pack of 100', 150, 130, 20, null],
                            ['Softia Serviettes 2-Ply', 'Pack of 50', 160, 140, 20, null],
                        ],
                    ],
                    [
                        'name' => 'Hand & Kitchen Towels',
                        'blurb' => 'Fast-absorbing towels for washrooms and kitchens.',
                        'products' => [
                            ['Softia Kitchen Towel 2-Pack', '2 rolls', 260, 230, 12, null],
                            ['Softia Z-Fold Hand Towels', 'Carton of 20 packs', null, 4200, 2, null],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'chemical-care',
                'code' => 'CC',
                'name' => 'Professional Chemical Care',
                'menu_label' => 'Chemical Care',
                'tagline' => 'Soaps, detergents & cleaners',
                'description' => 'Effective cleaning chemicals trusted by facilities, schools and hospitality.',
                'image' => 'images/cleaner-bottle.jpg',
                'sections' => [
                    [
                        'name' => 'Hand Soaps',
                        'blurb' => 'Gentle, hygienic liquid hand soap for frequent use.',
                        'products' => [
                            ['Softia Liquid Hand Soap 500ml', '500 ml', 250, 220, 12, null],
                            ['Softia Liquid Hand Soap 5L Refill', '5 litres', 1200, 1050, 4, null],
                        ],
                    ],
                    [
                        'name' => 'Detergents',
                        'blurb' => 'Concentrated detergents for laundry and dishwashing.',
                        'products' => [
                            ['Softia Multi-Purpose Detergent 5L', '5 litres', 950, 850, 4, null],
                            ['Softia Dishwashing Liquid 1L', '1 litre', 280, 240, 12, null],
                        ],
                    ],
                    [
                        'name' => 'Toilet & Surface Cleaners',
                        'blurb' => 'Powerful cleaners that remove stains and kill germs.',
                        'products' => [
                            ['Softia Toilet Cleaner 750ml', '750 ml', 320, 280, 12, 'images/cleaner-bottle.jpg'],
                            ['Softia Surface Disinfectant 5L', '5 litres', 1350, 1200, 4, null],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'institutional-hardware',
                'code' => 'IH',
                'name' => 'Institutional Hardware',
                'menu_label' => 'Institutional Hardware',
                'tagline' => 'Dispensers & bins',
                'description' => 'Durable washroom hardware built for high-traffic institutions.',
                'image' => null,
                'sections' => [
                    [
                        'name' => 'Dispensers',
                        'blurb' => 'Wall-mounted dispensers designed for Softia refills.',
                        'products' => [
                            ['Jumbo Roll Tissue Dispenser', 'Each', null, 2800, 1, null],
                            ['Liquid Soap Dispenser 1L', 'Each', 1500, 1300, 4, null],
                            ['Z-Fold Towel Dispenser', 'Each', null, 2500, 1, null],
                        ],
                    ],
                    [
                        'name' => 'Bins',
                        'blurb' => 'Hygienic bins for washrooms, offices and kitchens.',
                        'products' => [
                            ['Pedal Bin 20L', 'Each', 1800, 1600, 4, null],
                            ['Sanitary Bin 15L', 'Each', null, 2200, 2, null],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'aluminium-foil-packaging',
                'code' => 'AF',
                'name' => 'Aluminium Foil & Food Packaging',
                'menu_label' => 'Aluminium Foil',
                'tagline' => 'Foil, containers & wraps',
                'description' => 'Food-safe foil and packaging for kitchens, caterers and takeaways.',
                'image' => null,
                'sections' => [
                    [
                        'name' => 'Foil Rolls',
                        'blurb' => 'Heavy-duty, food-safe aluminium foil.',
                        'products' => [
                            ['Softia Aluminium Foil 10m', '10 m roll', 220, 190, 24, null],
                            ['Softia Catering Foil 90m', '90 m roll', 1450, 1300, 6, null],
                        ],
                    ],
                    [
                        'name' => 'Food Containers',
                        'blurb' => 'Foil containers with lids for takeaway and catering.',
                        'products' => [
                            ['Foil Container with Lid 450ml', 'Pack of 50', 950, 850, 10, null],
                            ['Foil Container with Lid 1000ml', 'Pack of 50', null, 1350, 10, null],
                        ],
                    ],
                ],
            ],
        ];
    }
}
