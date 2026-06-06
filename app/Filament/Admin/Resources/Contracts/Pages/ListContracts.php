<?php

namespace App\Filament\Admin\Resources\Contracts\Pages;

use App\Filament\Admin\Resources\Contracts\ContractResource;
use App\Models\Contract;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListContracts extends ListRecords
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('newFromTemplate')
                ->label('New from Template')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->modalHeading('Select a Template')
                ->modalDescription('Choose a contract template to pre-fill the create form.')
                ->modalSubmitActionLabel('Use Template')
                ->form([
                    Select::make('template_id')
                        ->label('Template')
                        ->options(fn () => Contract::where('is_template', true)
                            ->orderBy('subject')
                            ->pluck('subject', 'id'))
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    return redirect(ContractResource::getUrl('create', ['template_id' => $data['template_id']]));
                }),
        ];
    }
}
