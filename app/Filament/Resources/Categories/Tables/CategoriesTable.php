<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('parent')->withCount('products'))
            ->defaultSort(fn (Builder $query) => $query
                ->orderByRaw('COALESCE(parent_id, id)')
                ->orderByRaw('parent_id IS NOT NULL')
                ->orderBy('sort_order'))
            ->columns([
                ImageColumn::make('image')
                    ->state(fn (Category $record): ?string => $record->imageUrl())
                    ->square(),
                TextColumn::make('name')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Category $record): ?string => $record->parent ? "Section of {$record->parent->name}" : 'Product line'),
                TextColumn::make('slug')
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('products_count')
                    ->label('Products')
                    ->badge(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Visible'),
            ])
            ->filters([
                TernaryFilter::make('product_lines')
                    ->label('Type')
                    ->placeholder('All')
                    ->trueLabel('Product lines')
                    ->falseLabel('Sections')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('parent_id'),
                        false: fn (Builder $query) => $query->whereNotNull('parent_id'),
                    ),
            ])
            ->recordActions([
                Action::make('viewOnSite')
                    ->label('View')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (Category $record): string => $record->isProductLine()
                        ? route('product-lines.show', $record)
                        : route('product-lines.show', [$record->parent, 'section' => $record->slug]))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
