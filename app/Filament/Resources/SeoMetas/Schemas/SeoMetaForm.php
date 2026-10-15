<?php

namespace App\Filament\Resources\SeoMetas\Schemas;

use App\Filament\Forms\SeoFields;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\SeoMeta;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SeoMetaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Applies to')
                    ->schema([
                        Radio::make('target')
                            ->hiddenLabel()
                            ->options([
                                'page' => 'A website page',
                                'record' => 'A product, product line, page or article',
                            ])
                            ->default('page')
                            ->inline()
                            ->live()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Radio $component, ?SeoMeta $record): void {
                                if ($record) {
                                    $component->state($record->route_name ? 'page' : 'record');
                                }
                            }),
                        Select::make('route_name')
                            ->label('Page')
                            ->options(config('shop.seo_pages'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->visible(fn (Get $get): bool => $get('target') === 'page'),
                        MorphToSelect::make('seoable')
                            ->label('Record')
                            ->types([
                                Type::make(Product::class)->titleAttribute('name'),
                                Type::make(Category::class)->titleAttribute('name')->label('Product line / section'),
                                Type::make(Page::class)->titleAttribute('title'),
                                Type::make(Post::class)->titleAttribute('title')->label('News article'),
                            ])
                            ->searchable()
                            ->required()
                            ->visible(fn (Get $get): bool => $get('target') === 'record'),
                    ])
                    ->columnSpanFull(),
                Section::make('Search & sharing')
                    ->schema(SeoFields::fields())
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Clear whichever target was not chosen, since hidden fields aren't saved.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalizeTarget(array $data): array
    {
        if (filled($data['route_name'] ?? null)) {
            $data['seoable_type'] = null;
            $data['seoable_id'] = null;
        } else {
            $data['route_name'] = null;
        }

        return $data;
    }
}
