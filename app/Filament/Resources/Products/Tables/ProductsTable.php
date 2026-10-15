<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Category;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('category.parent'))
            ->defaultSort('name')
            ->columns([
                ImageColumn::make('image')
                    ->state(fn (Product $record): ?string => $record->imageUrl())
                    ->square(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Product $record): string => $record->sku),
                TextColumn::make('category.name')
                    ->label('Section')
                    ->description(fn (Product $record): ?string => $record->category->parent?->name)
                    ->sortable(),
                TextColumn::make('retail_price')
                    ->label('Retail')
                    ->money('KES')
                    ->placeholder('Quote only')
                    ->sortable(),
                TextColumn::make('wholesale_price')
                    ->label('Wholesale')
                    ->money('KES')
                    ->description(fn (Product $record): string => "from {$record->min_wholesale_qty}")
                    ->toggleable(),
                TextColumn::make('stock')
                    ->badge()
                    ->color(fn (Product $record): string => match (true) {
                        ! $record->isInStock() => 'danger',
                        $record->isLowStock() => 'warning',
                        default => 'success',
                    })
                    ->sortable(),
                ToggleColumn::make('is_featured')
                    ->label('Featured'),
                ToggleColumn::make('is_active')
                    ->label('Visible'),
            ])
            ->filters([
                SelectFilter::make('product_line')
                    ->label('Product line')
                    ->options(fn (): array => Category::query()->whereNull('parent_id')->orderBy('sort_order')->pluck('name', 'id')->all())
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn (Builder $query, string $lineId) => $query->whereHas(
                            'category',
                            fn (Builder $category) => $category->whereKey($lineId)->orWhere('parent_id', $lineId),
                        ),
                    )),
                TernaryFilter::make('is_active')
                    ->label('Visible on website'),
                Filter::make('quote_only')
                    ->label('Quote only')
                    ->query(fn (Builder $query) => $query->whereNull('retail_price')),
                Filter::make('low_stock')
                    ->label('Low or out of stock')
                    ->query(fn (Builder $query) => $query->whereColumn('stock', '<=', 'low_stock_threshold')),
            ])
            ->recordActions([
                Action::make('viewOnSite')
                    ->label('View')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (Product $record): string => route('products.show', [$record->productLine(), $record]))
                    ->openUrlInNewTab(),
                ReplicateAction::make()
                    ->label('Duplicate')
                    ->excludeAttributes(['slug', 'sku'])
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['name'] .= ' (copy)';

                        return $data;
                    })
                    ->beforeReplicaSaved(function (Product $replica): void {
                        $suffix = Str::lower(Str::random(4));
                        $replica->slug = Str::slug($replica->name).'-'.$suffix;
                        $replica->sku = 'COPY-'.Str::upper($suffix);
                        $replica->is_active = false;
                    })
                    ->successRedirectUrl(fn (Product $replica): string => route('filament.admin.resources.products.edit', $replica)),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('hide')
                        ->label('Hide from website')
                        ->icon(Heroicon::OutlinedEyeSlash)
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('show')
                        ->label('Show on website')
                        ->icon(Heroicon::OutlinedEye)
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
