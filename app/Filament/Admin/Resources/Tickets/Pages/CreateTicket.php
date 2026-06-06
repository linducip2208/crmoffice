<?php

namespace App\Filament\Admin\Resources\Tickets\Pages;

use App\Filament\Admin\Resources\Tickets\TicketResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Http;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('suggestKb')
                ->label('Suggest KB Articles')
                ->icon('heroicon-o-light-bulb')
                ->color('warning')
                ->modalHeading('Suggested Knowledge Base Articles')
                ->modalContent(function () {
                    $data = $this->form->getState();
                    $subject = $data['subject'] ?? '';
                    $body = $data['body'] ?? '';

                    if (empty($subject) || empty($body)) {
                        return <<<HTML
                            <div class="rounded-lg bg-warning-50 p-4 text-warning-700 text-sm">
                                Please fill in the <strong>Subject</strong> and <strong>Body</strong> fields first, then click this button again.
                            </div>
                        HTML;
                    }

                    $url = route('admin.ai.suggest-kb');

                    try {
                        $resp = Http::timeout(60)->post($url, [
                            'subject' => $subject,
                            'body' => $body,
                        ]);

                        if ($resp->successful()) {
                            $articles = $resp->json('articles', []);

                            if (empty($articles)) {
                                return <<<HTML
                                    <div class="rounded-lg bg-gray-50 p-4 text-gray-600 text-sm text-center">
                                        No matching KB articles found for this topic.
                                    </div>
                                HTML;
                            }

                            $html = '<div class="space-y-3">';
                            foreach ($articles as $article) {
                                $title = e($article['title'] ?? 'Untitled');
                                $excerpt = e($article['excerpt'] ?? '');
                                $slug = e($article['slug'] ?? '#');
                                $url = url("/docs/{$slug}");
                                $html .= <<<HTML
                                <a href="{$url}" target="_blank" class="block rounded-lg border border-gray-200 p-3 hover:border-primary-300 hover:bg-primary-50 transition-colors">
                                    <span class="text-sm font-semibold text-primary-600">{$title}</span>
                                    <p class="mt-1 text-xs text-gray-500 line-clamp-2">{$excerpt}</p>
                                </a>
HTML;

                            }
                            $html .= '</div>';

                            return $html;
                        }

                        $error = e($resp->json('error', 'Could not search KB articles.'));

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
        ];
    }
}
