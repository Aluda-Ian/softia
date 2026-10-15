<?php

namespace App\Filament\Resources\SeoMetas\Pages;

use App\Filament\Resources\SeoMetas\Schemas\SeoMetaForm;
use App\Filament\Resources\SeoMetas\SeoMetaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSeoMeta extends CreateRecord
{
    protected static string $resource = SeoMetaResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return SeoMetaForm::normalizeTarget($data);
    }
}
