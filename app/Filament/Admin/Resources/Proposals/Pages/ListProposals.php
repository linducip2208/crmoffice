<?php

namespace App\Filament\Admin\Resources\Proposals\Pages;

use App\Filament\Admin\Resources\Proposals\ProposalResource;
use App\Models\Proposal;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListProposals extends ListRecords
{
    protected static string $resource = ProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('newFromTemplate')
                ->label('New from Template')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->modalHeading('Select a Template')
                ->modalDescription('Choose a proposal template to pre-fill the create form.')
                ->modalSubmitActionLabel('Use Template')
                ->form([
                    Select::make('template_id')
                        ->label('Template')
                        ->options(fn () => Proposal::where('is_template', true)
                            ->orderBy('subject')
                            ->pluck('subject', 'id'))
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    return redirect(ProposalResource::getUrl('create', ['template_id' => $data['template_id']]));
                }),
        ];
    }
}
