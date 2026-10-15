<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn (User $record): bool => $record->is(auth()->user())),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $attributes = UserResource::accountAttributes($data);

        // Nobody can lock themselves out by removing their own access.
        if ($record->is(auth()->user())) {
            unset($attributes['is_admin'], $attributes['is_super_admin']);
        }

        $record->forceFill($attributes)->save();

        return $record;
    }
}
