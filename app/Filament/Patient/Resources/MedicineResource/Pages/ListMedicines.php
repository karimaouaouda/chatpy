<?php

namespace App\Filament\Patient\Resources\MedicineResource\Pages;

use App\Filament\Patient\Resources\MedicineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedicines extends ListRecords
{
    protected static string $resource = MedicineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
