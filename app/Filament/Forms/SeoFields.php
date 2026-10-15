<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;

/**
 * Meta title, description, Open Graph image, canonical URL and robots fields,
 * shared by the SEO Manager and the product / category forms.
 */
class SeoFields
{
    /**
     * @return array<Component|Field>
     */
    public static function fields(): array
    {
        return [
            TextInput::make('meta_title')
                ->maxLength(70)
                ->helperText('Shown as the search result headline. Aim for 50–60 characters.'),
            Textarea::make('meta_description')
                ->rows(3)
                ->maxLength(500)
                ->helperText('Shown under the headline in search results. Aim for 150–160 characters.'),
            WebpImageUpload::make('og_image', 'seo')
                ->label('Open Graph image')
                ->helperText('Preview image for WhatsApp, Facebook and X shares. 1200 × 630 works best.'),
            TextInput::make('canonical_url')
                ->label('Canonical URL')
                ->url()
                ->maxLength(255)
                ->helperText('Leave empty to use the page’s own address.'),
            Select::make('robots')
                ->options([
                    'index, follow' => 'Index & follow (default)',
                    'noindex, follow' => 'Hide from search, follow links',
                    'noindex, nofollow' => 'Hide from search completely',
                ])
                ->default('index, follow')
                ->selectablePlaceholder(false),
        ];
    }

    /**
     * Collapsible section saved to the record's `seo` relationship; nothing is
     * stored unless at least one override is filled in.
     */
    public static function section(): Section
    {
        return Section::make('SEO')
            ->description('Optional overrides. Defaults come from the name, description and image.')
            ->relationship('seo', condition: fn (?array $state): bool => filled($state['meta_title'] ?? null)
                || filled($state['meta_description'] ?? null)
                || filled($state['og_image'] ?? null)
                || filled($state['canonical_url'] ?? null)
                || ($state['robots'] ?? 'index, follow') !== 'index, follow')
            ->schema(self::fields())
            ->collapsible()
            ->collapsed()
            ->columnSpanFull();
    }
}
