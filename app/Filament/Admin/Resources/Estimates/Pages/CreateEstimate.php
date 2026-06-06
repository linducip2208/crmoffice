<?php

namespace App\Filament\Admin\Resources\Estimates\Pages;

use App\Filament\Admin\Resources\Estimates\EstimateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEstimate extends CreateRecord
{
    protected static string $resource = EstimateResource::class;
}
