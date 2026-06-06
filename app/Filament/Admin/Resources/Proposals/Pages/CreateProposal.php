<?php

namespace App\Filament\Admin\Resources\Proposals\Pages;

use App\Filament\Admin\Resources\Proposals\Actions\DraftWithAi;
use App\Filament\Admin\Resources\Proposals\ProposalResource;
use App\Models\Proposal;
use Filament\Resources\Pages\CreateRecord;

class CreateProposal extends CreateRecord
{
    protected static string $resource = ProposalResource::class;

    public function mount(): void
    {
        parent::mount();

        $templateId = request()->query('template_id');
        if ($templateId && $template = Proposal::find($templateId)) {
            $this->form->fill([
                'subject' => $template->subject,
                'content' => $template->content,
                'client_id' => $template->client_id,
                'lead_id' => $template->lead_id,
                'total' => $template->total,
                'currency_id' => $template->currency_id,
                'open_until' => $template->open_until,
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DraftWithAi::make(),
        ];
    }
}
