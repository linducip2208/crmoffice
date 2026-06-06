<?php

namespace App\Filament\Admin\Resources\Leads\Pages;

use App\Actions\Crm\ConvertLeadToClient;
use App\Filament\Admin\Actions\MeetingNotesAction;
use App\Filament\Admin\Resources\Leads\LeadResource;
use App\Services\AiLeadScoringService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('scoreLead')
                ->label('Score Lead')
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->action(function () {
                    $result = app(AiLeadScoringService::class)->scoreLead($this->record);

                    $notification = Notification::make()
                        ->title("Lead Score: {$result['score']}/100 — " . strtoupper($result['level']));

                    $factorText = collect($result['factors'] ?? [])
                        ->map(fn ($v, $k) => "{$k}: {$v}")
                        ->implode(', ');

                    $body = $result['recommendation'] ?? $factorText;

                    if ($result['ai_reasoning'] ?? null) {
                        $body .= "\n\nAI Insight: {$result['ai_reasoning']}";
                    }

                    $notification->body($body);

                    match ($result['level'] ?? 'cold') {
                        'hot' => $notification->success(),
                        'warm' => $notification->warning(),
                        default => $notification->info(),
                    };

                    $notification->send();

                    return redirect(request()->header('Referer'));
                }),

            Action::make('convertToClient')
                ->label('Convert to Client')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('success')
                ->visible(fn () => ! $this->record->converted_to_client_id)
                ->requiresConfirmation()
                ->modalDescription('Convert this lead into a new Client record. The lead will be marked as converted.')
                ->action(function () {
                    $client = app(ConvertLeadToClient::class)->handle($this->record);

                    Notification::make()
                        ->title('Lead converted')
                        ->body("New client created: {$client->company_name}")
                        ->success()
                        ->send();

                    return redirect('/admin/clients/' . $client->id . '/edit');
                }),

            Action::make('viewClient')
                ->label('View Converted Client')
                ->icon('heroicon-o-building-office-2')
                ->color('info')
                ->visible(fn () => (bool) $this->record->converted_to_client_id)
                ->url(fn () => '/admin/clients/' . $this->record->converted_to_client_id . '/edit'),

            MeetingNotesAction::make('meetingNotes')
                ->relatedType('lead')
                ->relatedId($this->record->id),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
