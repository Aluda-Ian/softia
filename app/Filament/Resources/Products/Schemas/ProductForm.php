<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Forms\SeoFields;
use App\Filament\Forms\WebpImageUpload;
use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('Product')
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, Get $get, ?string $state, string $operation): void {
                                    if ($operation === 'create' || blank($get('slug'))) {
                                        $set('slug', Str::slug($state ?? ''));
                                    }
                                })
                                ->columnSpanFull(),
                            TextInput::make('slug')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                            TextInput::make('sku')
                                ->label('SKU')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                            Select::make('category_id')
                                ->label('Section')
                                ->options(fn (): array => self::sectionOptions())
                                ->searchable()
                                ->required(),
                            TextInput::make('unit')
                                ->placeholder('e.g. Pack of 6, 5 litres')
                                ->maxLength(255),
                            TextInput::make('short_description')
                                ->maxLength(255)
                                ->helperText('One line shown on product cards and in search results.')
                                ->columnSpanFull(),
                            Textarea::make('description')
                                ->rows(5)
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    Section::make('Pricing')
                        ->schema([
                            TextInput::make('retail_price')
                                ->numeric()
                                ->minValue(0)
                                ->prefix('KES')
                                ->helperText('Leave empty to sell on quotation only.'),
                            TextInput::make('wholesale_price')
                                ->numeric()
                                ->minValue(0)
                                ->prefix('KES'),
                            TextInput::make('min_wholesale_qty')
                                ->label('Wholesale from (qty)')
                                ->numeric()
                                ->integer()
                                ->minValue(1)
                                ->default(1)
                                ->required(),
                        ])
                        ->columns(3),
                    SeoFields::section(),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    Section::make('Visibility')
                        ->schema([
                            Toggle::make('is_active')
                                ->label('Visible on website')
                                ->default(true),
                            Toggle::make('is_featured')
                                ->label('Featured on home page'),
                        ]),
                    Section::make('Inventory')
                        ->schema([
                            TextInput::make('stock')
                                ->numeric()
                                ->integer()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                            TextInput::make('low_stock_threshold')
                                ->label('Low stock alert at')
                                ->numeric()
                                ->integer()
                                ->minValue(0)
                                ->default(10)
                                ->required(),
                        ]),
                    Section::make('Images')
                        ->schema([
                            WebpImageUpload::make('image', 'products')
                                ->label('Main image'),
                            WebpImageUpload::make('gallery', 'products')
                                ->label('Gallery')
                                ->multiple()
                                ->reorderable()
                                ->maxFiles(8),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }

    /**
     * Sections grouped under their product line, plus the lines themselves.
     *
     * @return array<string, array<int, string>>
     */
    private static function sectionOptions(): array
    {
        return Category::query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (Category $line): array => [
                $line->name => [$line->id => "{$line->name} (general)"]
                    + $line->children->pluck('name', 'id')->all(),
            ])
            ->all();
    }
}
