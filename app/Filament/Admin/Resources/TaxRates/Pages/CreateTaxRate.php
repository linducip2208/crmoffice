<?php

namespace App\Filament\Admin\Resources\TaxRates\Pages;

use App\Filament\Admin\Resources\TaxRates\TaxRateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxRate extends CreateRecord
{
    protected static string $resource = TaxRateResource::class;
}
