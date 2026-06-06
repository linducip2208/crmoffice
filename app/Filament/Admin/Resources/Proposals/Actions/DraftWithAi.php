<?php

namespace App\Filament\Admin\Resources\Proposals\Actions;

use App\Actions\Ai\DraftProposalWithAi;
use App\Models\Client;
use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

class DraftWithAi extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'draftWithAi';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Draft with AI')
            ->icon('heroicon-o-sparkles')
            ->color('warning')
            ->modalDescription('AI will draft proposal content based on the selected client/lead and your instructions. Review and edit before saving.')
            ->modalSubmitActionLabel('Generate Draft')
            ->mountUsing(function (Action $action, ?Schema $schema) {
                $livewire = $action->getLivewire();
                $pageData = $livewire->data ?? [];

                $contextLines = [];
                $clientId = $pageData['client_id'] ?? null;
                $leadId = $pageData['lead_id'] ?? null;

                if ($clientId && $client = Client::find($clientId)) {
                    $contextLines[] = "Client: {$client->company_name}";
                    if ($client->industry) {
                        $contextLines[] = "Industry: {$client->industry}";
                    }
                    if ($client->website) {
                        $contextLines[] = "Website: {$client->website}";
                    }
                }

                if ($leadId && $lead = Lead::find($leadId)) {
                    $contextLines[] = "Lead: {$lead->name}" . ($lead->company ? " ({$lead->company})" : '');
                    if ($lead->description) {
                        $contextLines[] = "Notes: {$lead->description}";
                    }
                }

                $schema?->fill([
                    'context_display' => $contextLines ? implode("\n", $contextLines) : '— No client or lead selected yet.',
                ]);
            })
            ->form([
                Forms\Components\Placeholder::make('context_display')
                    ->label('Client / Lead Context')
                    ->content(fn ($get) => nl2br(e($get('context_display') ?: '—'))),
                Forms\Components\Textarea::make('instructions')
                    ->label('Extra Instructions')
                    ->placeholder('Describe the service, deliverables, scope, or any specific requirements...')
                    ->rows(4)
                    ->helperText('Be specific about what you are proposing, timeline, budget hints.'),
                Forms\Components\Select::make('tone')
                    ->label('Tone')
                    ->options([
                        'formal' => 'Formal',
                        'friendly' => 'Friendly',
                        'casual' => 'Casual',
                    ])
                    ->default('formal')
                    ->required(),
            ])
            ->action(function (array $data) {
                try {
                    $livewire = $this->getLivewire();
                    $pageData = $livewire->data ?? [];

                    $content = app(DraftProposalWithAi::class)->handle(
                        subject: $pageData['subject'] ?? 'Proposal',
                        clientId: $pageData['client_id'] ?? null,
                        leadId: $pageData['lead_id'] ?? null,
                        tone: $data['tone'],
                        instructions: $data['instructions'] ?? null,
                    );

                    $pageData['content'] = $content;
                    $livewire->form->fill($pageData);

                    Notification::make()
                        ->title('AI draft generated')
                        ->body('Proposal content has been populated. Review and edit before saving.')
                        ->success()
                        ->send();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('AI draft failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
