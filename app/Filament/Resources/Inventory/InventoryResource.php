<?php

namespace App\Filament\Resources\Inventory;

use App\Filament\Concerns\RequiresTeamPermission;
use App\Filament\Resources\Inventory\Pages\ManageInventory;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * Stock-focused view of products with inline editing and quick adjustments.
 */
class InventoryResource extends Resource
{
    use RequiresTeamPermission;

    public static function teamPermission(): string
    {
        return 'inventory';
    }

    protected static ?string $model = Product::class;

    protected static ?string $slug = 'inventory';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Inventory';

    protected static ?string $modelLabel = 'stock item';

    protected static ?string $pluralModelLabel = 'Inventory';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $lowStock = Product::query()->active()->whereColumn('stock', '<=', 'low_stock_threshold')->count();

        return $lowStock > 0 ? (string) $lowStock : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Low or out of stock';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('category.parent'))
            ->defaultSort('stock')
            ->columns([
                TextColumn::make('name')
                    ->searchable(['name', 'sku'])
                    ->weight('bold')
                    ->description(fn (Product $record): string => $record->sku),
                TextColumn::make('category.name')
                    ->label('Section')
                    ->description(fn (Product $record): ?string => $record->category->parent?->name),
                TextColumn::make('stock_status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (Product $record): string => match (true) {
                        ! $record->isInStock() => 'Out of stock',
                        $record->isLowStock() => 'Low stock',
                        default => 'In stock',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Out of stock' => 'danger',
                        'Low stock' => 'warning',
                        default => 'success',
                    }),
                TextInputColumn::make('stock')
                    ->type('number')
                    ->rules(['required', 'integer', 'min:0'])
                    ->sortable(),
                TextInputColumn::make('low_stock_threshold')
                    ->label('Alert at')
                    ->type('number')
                    ->rules(['required', 'integer', 'min:0']),
            ])
            ->filters([
                Filter::make('low_stock')
                    ->label('Low or out of stock')
                    ->query(fn (Builder $query) => $query->whereColumn('stock', '<=', 'low_stock_threshold')),
                Filter::make('out_of_stock')
                    ->label('Out of stock')
                    ->query(fn (Builder $query) => $query->where('stock', 0)),
            ])
            ->recordActions([
                Action::make('adjust')
                    ->label('Adjust')
                    ->icon(Heroicon::OutlinedPlusCircle)
                    ->schema(self::adjustmentFields())
                    ->action(function (Product $record, array $data): void {
                        self::applyAdjustment($record, $data);

                        Notification::make()->title("{$record->name}: {$record->stock} in stock")->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('adjustSelected')
                    ->label('Adjust stock')
                    ->icon(Heroicon::OutlinedPlusCircle)
                    ->schema(self::adjustmentFields())
                    ->action(function (Collection $records, array $data): void {
                        $records->each(fn (Product $record) => self::applyAdjustment($record, $data));

                        Notification::make()->title("Stock updated for {$records->count()} products")->success()->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    /**
     * @return array<int, Radio|TextInput>
     */
    private static function adjustmentFields(): array
    {
        return [
            Radio::make('mode')
                ->options([
                    'add' => 'Add received stock',
                    'remove' => 'Remove (damaged / used)',
                    'set' => 'Set exact count',
                ])
                ->default('add')
                ->required(),
            TextInput::make('quantity')
                ->numeric()
                ->integer()
                ->minValue(0)
                ->required(),
        ];
    }

    /**
     * @param  array{mode: string, quantity: int|string}  $data
     */
    private static function applyAdjustment(Product $product, array $data): void
    {
        $quantity = (int) $data['quantity'];

        $product->update(['stock' => match ($data['mode']) {
            'add' => $product->stock + $quantity,
            'remove' => max(0, $product->stock - $quantity),
            default => $quantity,
        }]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageInventory::route('/'),
        ];
    }
}
