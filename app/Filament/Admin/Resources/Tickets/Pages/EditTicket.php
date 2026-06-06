<?php

namespace App\Filament\Admin\Resources\Tickets\Pages;

use App\Filament\Admin\Resources\Tickets\TicketResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Http;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('summarize')
                ->label('Summarize with AI')
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->modalHeading('AI Conversation Summary')
                ->modalContent(function () {
                    $ticket = $this->getRecord();
                    $url = route('admin.ai.summarize-ticket', ['ticket' => $ticket->id]);

                    try {
                        $resp = Http::timeout(60)->post($url);
                        if ($resp->successful()) {
                            $summary = e($resp->json('summary', 'No summary generated.'));

                            return <<<HTML
                                <div class="prose max-w-none text-sm leading-relaxed whitespace-pre-wrap">{$summary}</div>
                            HTML;
                        }

                        $error = e($resp->json('error', 'AI feature not available.'));

                        return <<<HTML
                            <div class="rounded-lg bg-danger-50 p-4 text-danger-600 text-sm">{$error}</div>
                        HTML;
                    } catch (\Throwable $e) {
                        $msg = e($e->getMessage());

                        return <<<HTML
                            <div class="rounded-lg bg-danger-50 p-4 text-danger-600 text-sm">Request failed: {$msg}</div>
                        HTML;
                    }
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),

            Action::make('draftReply')
                ->label('Draft Reply with AI')
                ->icon('heroicon-o-pencil-square')
                ->color('success')
                ->modalHeading('AI Reply Draft')
                ->modalContent(function () {
                    $ticket = $this->getRecord();
                    $url = route('admin.ai.draft-reply', ['ticket' => $ticket->id]);

                    try {
                        $resp = Http::timeout(60)->post($url);
                        if ($resp->successful()) {
                            $draft = e($resp->json('draft', ''));

                            return <<<HTML
                                <div class="space-y-3">
                                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                        <div class="prose max-w-none text-sm leading-relaxed whitespace-pre-wrap">{$draft}</div>
                                    </div>
                                    <p class="text-xs text-gray-500">Copy the text above and paste it into a new reply on this ticket.</p>
                                </div>
                            HTML;
                        }

                        $error = e($resp->json('error', 'AI feature not available.'));

                        return <<<HTML
                            <div class="rounded-lg bg-danger-50 p-4 text-danger-600 text-sm">{$error}</div>
                        HTML;
                    } catch (\Throwable $e) {
                        $msg = e($e->getMessage());

                        return <<<HTML
                            <div class="rounded-lg bg-danger-50 p-4 text-danger-600 text-sm">Request failed: {$msg}</div>
                        HTML;
                    }
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
