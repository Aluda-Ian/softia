<?php

namespace App\Filament\Resources\PushNotifications\Schemas;

use App\Filament\Forms\WebpImageUpload;
use App\Models\Category;
use App\Models\Post;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class PushNotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Message')
                    ->description('Sent instantly to every phone and computer that turned on Softia notifications.')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(60)
                            ->live(debounce: 400)
                            ->placeholder('Back to school: 15% off jumbo rolls')
                            ->helperText('Keep it short: phones cut off long titles.'),
                        Textarea::make('body')
                            ->label('Text')
                            ->required()
                            ->rows(3)
                            ->maxLength(160)
                            ->live(debounce: 400),
                        Select::make('link_preset')
                            ->label('Opens')
                            ->options(fn (): array => self::linkOptions())
                            ->placeholder('Choose a page')
                            ->dehydrated(false)
                            ->live()
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $state && $set('url', $state)),
                        TextInput::make('url')
                            ->label('Link')
                            ->url()
                            ->maxLength(255)
                            ->default(url('/'))
                            ->helperText('Pick a page above or paste any link on the Softia site.'),
                        WebpImageUpload::make('image', 'push')
                            ->label('Large image (optional)')
                            ->helperText('Shown on Android and desktop. Wide images (2:1) work best.'),
                    ])
                    ->columnSpan(['lg' => 2]),

                Section::make('Preview')
                    ->schema([
                        View::make('filament.push-preview')
                            ->viewData(fn (Get $get): array => [
                                'title' => $get('title'),
                                'body' => $get('body'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ]);
    }

    /**
     * @return array<string, array<string, string>>
     */
    private static function linkOptions(): array
    {
        return [
            'Pages' => [
                url('/') => 'Home page',
                route('news.index') => 'News',
                route('contact') => 'Contact',
            ],
            'Product lines' => Category::query()->productLines()->get()
                ->mapWithKeys(fn (Category $line): array => [route('product-lines.show', $line) => $line->name])
                ->all(),
            'Latest news' => Post::query()->published()->latest('published_at')->take(5)->get()
                ->mapWithKeys(fn (Post $post): array => [route('news.show', $post) => $post->title])
                ->all(),
        ];
    }
}
