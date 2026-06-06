<?php

namespace App\Filament\Admin\Actions;

use App\Services\AiLeadScoringService;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;

class BulkScoreLeadsAction extends BulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'bulkScoreLeads';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Score Leads')
            ->icon('heroicon-o-sparkles')
            ->color('info')
            ->action(function ($records) {
                $service = app(AiLeadScoringService::class);
                $hot = 0;
                $warm = 0;
                $cold = 0;

                foreach ($records as $lead) {
                    $result = $service->scoreLead($lead);
                    match ($result['level'] ?? 'cold') {
                        'hot' => $hot++,
                        'warm' => $warm++,
                        default => $cold++,
                    };
                }

                Notification::make()
                    ->title('Lead scoring complete')
                    ->body("Hot: {$hot}, Warm: {$warm}, Cold: {$cold} — out of " . count($records) . " leads")
                    ->success()
                    ->send();
            });
    }
}
