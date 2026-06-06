<?php

namespace App\Filament\Admin\Pages;

use App\Jobs\DispatchWebhook;
use App\Models\WebhookDelivery;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class WebhookDeliveries extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $navigationLabel = 'Webhook Deliveries';

    protected static string|\UnitEnum|null $navigationGroup = 'Integrasi';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'webhook-deliveries';

    protected string $view = 'filament.admin.pages.webhook-deliveries';

    public function table(Table $table): Table
    {
        return $table
            ->query(WebhookDelivery::query()->with('webhook')->latest('id'))
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('webhook.event')->label('Event')->badge(),
                TextColumn::make('webhook.url')->limit(40),
                TextColumn::make('status_code')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 200 && $state < 300 => 'success',
                        $state >= 400 => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('attempt'),
                TextColumn::make('delivered_at')->dateTime()->placeholder('—'),
            ])
            ->actions([
                Action::make('replay')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (WebhookDelivery $record) {
                        $payload = $record->payload['data'] ?? $record->payload ?? [];
                        $event = $record->payload['event'] ?? $record->webhook?->event;
                        DispatchWebhook::dispatch($record->webhook_id, $event, $payload);

                        Notification::make()->title('Re-dispatched')->success()->send();
                    }),
            ]);
    }
}
