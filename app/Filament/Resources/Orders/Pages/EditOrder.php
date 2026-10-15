<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /**
     * Recalculate totals from the (possibly re-priced) items, and move a
     * pending quotation to "Quoted" once every line has a price.
     */
    protected function afterSave(): void
    {
        /** @var Order $order */
        $order = $this->getRecord();
        $order->recalculateTotals();

        if ($order->isQuote() && $order->status === OrderStatus::Pending && ! $order->hasUnpricedItems()) {
            $order->update(['status' => OrderStatus::Quoted]);
        }

        $this->refreshFormData(['status', 'items']);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
