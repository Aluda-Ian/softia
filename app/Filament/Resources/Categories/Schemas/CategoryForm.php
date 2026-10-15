<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Forms\SeoFields;
use App\Filament\Forms\WebpImageUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->schema([
                        Select::make('parent_id')
                            ->label('Belongs to product line')
                            ->relationship('parent', 'name', fn (Builder $query) => $query->whereNull('parent_id'))
                            ->placeholder('None — this is a product line')
                            ->helperText('Leave empty to create a top-level product line shown on the home page.')
                            ->live(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state, string $operation): void {
                                if ($operation === 'create' || blank($get('slug'))) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Used in the web address, e.g. /products/paper-products.'),
                        TextInput::make('menu_label')
                            ->label('Menu label')
                            ->maxLength(40)
                            ->regex('/^\S+(\s\S+)?$/')
                            ->validationMessages(['regex' => 'Use at most two words.'])
                            ->helperText('Short name for the main menu, two words at most. Defaults to the name.')
                            ->visible(fn (Get $get): bool => blank($get('parent_id'))),
                        TextInput::make('tagline')
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => blank($get('parent_id'))),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        WebpImageUpload::make('image', 'categories')
                            ->columnSpanFull(),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Visible on website')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                SeoFields::section(),
            ]);
    }
}
