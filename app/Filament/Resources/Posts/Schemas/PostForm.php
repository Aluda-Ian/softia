<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Forms\ContentEditor;
use App\Filament\Forms\SeoFields;
use App\Filament\Forms\WebpImageUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('Article')
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
                                ->prefix('/news/'),
                            Textarea::make('excerpt')
                                ->label('Summary')
                                ->rows(2)
                                ->maxLength(500)
                                ->helperText('Shown on the news list and used as the default search description.'),
                            ContentEditor::make('body', 'news'),
                        ]),
                    SeoFields::section(),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    Section::make('Publishing')
                        ->schema([
                            DateTimePicker::make('published_at')
                                ->label('Publish date')
                                ->seconds(false)
                                ->native(false)
                                ->helperText('Leave empty to keep as a draft. A future date schedules the article.'),
                        ]),
                    Section::make('Cover image')
                        ->schema([
                            WebpImageUpload::make('cover_image', 'news')
                                ->hiddenLabel(),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
