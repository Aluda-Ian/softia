<?php

namespace App\Filament\Resources\SeoMetas\Tables;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\SeoMeta;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SeoMetasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('seoable'))
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('target')
                    ->label('Applies to')
                    ->weight('bold')
                    ->state(fn (SeoMeta $record): string => self::targetLabel($record))
                    ->description(fn (SeoMeta $record): string => self::targetType($record)),
                TextColumn::make('meta_title')
                    ->placeholder('Default')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('meta_description')
                    ->placeholder('Default')
                    ->limit(70)
                    ->wrap()
                    ->toggleable(),
                ImageColumn::make('og_image')
                    ->label('Share image')
                    ->state(fn (SeoMeta $record): ?string => $record->ogImageUrl()),
                TextColumn::make('robots')
                    ->badge()
                    ->color(fn (string $state): string => str_starts_with($state, 'noindex') ? 'warning' : 'success'),
                TextColumn::make('updated_at')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('pages')
                    ->label('Type')
                    ->placeholder('All')
                    ->trueLabel('Website pages')
                    ->falseLabel('Products & product lines')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('route_name'),
                        false: fn (Builder $query) => $query->whereNull('route_name'),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function targetLabel(SeoMeta $record): string
    {
        if ($record->route_name) {
            return config("shop.seo_pages.{$record->route_name}", $record->route_name);
        }

        return $record->seoable?->name ?? $record->seoable?->title ?? 'Deleted record';
    }

    private static function targetType(SeoMeta $record): string
    {
        return match (true) {
            filled($record->route_name) => 'Website page',
            $record->seoable instanceof Product => 'Product',
            $record->seoable instanceof Page => 'Page',
            $record->seoable instanceof Post => 'News article',
            $record->seoable instanceof Category => $record->seoable->isProductLine() ? 'Product line' : 'Section',
            default => '—',
        };
    }
}
