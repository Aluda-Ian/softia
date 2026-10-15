<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Forms\ContentEditor;
use App\Filament\Forms\SeoFields;
use App\Models\Page;
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

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('Content')
                        ->schema([
                            TextInput::make('title')
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
                                ->prefix('/pages/'),
                            Textarea::make('excerpt')
                                ->label('Summary')
                                ->rows(2)
                                ->maxLength(500)
                                ->helperText('Shown under the page title and used as the default search description.'),
                            ContentEditor::make('body', 'pages'),
                        ]),
                    SeoFields::section(),
                ])->columnSpan(['lg' => 2]),

                Section::make('Publishing')
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true),
                        Select::make('footer_group')
                            ->label('Footer menu')
                            ->options(Page::FOOTER_GROUPS)
                            ->placeholder('Not in footer'),
                        TextInput::make('sort_order')
                            ->label('Order in menu')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                    ])
                    ->columnSpan(['lg' => 1]),
            ]);
    }
}
