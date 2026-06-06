<?php

namespace App\Filament\Admin\Resources\Contracts\Pages;

use App\Filament\Admin\Resources\Contracts\ContractResource;
use App\Models\Contract;
use Filament\Resources\Pages\CreateRecord;

class CreateContract extends CreateRecord
{
    protected static string $resource = ContractResource::class;

    public function mount(): void
    {
        parent::mount();

        $templateId = request()->query('template_id');
        if ($templateId && $template = Contract::find($templateId)) {
            $this->form->fill([
                'subject' => $template->subject,
                'content' => $template->content,
                'client_id' => $template->client_id,
                'start_date' => $template->start_date,
                'end_date' => $template->end_date,
                'contract_value' => $template->contract_value,
                'currency_id' => $template->currency_id,
                'notify_expiry_days_before' => $template->notify_expiry_days_before,
            ]);
        }
    }
}
